<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\exambank;
use App\Models\criteria;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Illuminate\Support\Facades\File;
use App\Enums\TypeProperty;
use App\Enums\TextDirection;
use App\Enums\TextControl;
use App\Enums\TableStyles;
use App\Enums\SheetName;
use App\Enums\PageSize;
use App\Enums\PageOrientation;
use App\Enums\Horizontal;
use App\Enums\Vertical;
use Validator;
use App\Enums\FormulaType;
use App\Enums\FontStyle;
use App\Enums\FormatNumber;
use App\Enums\FontUnderline;
use App\Enums\FontEffect;
use App\Enums\FontColor;
use App\Enums\Font;

class ExamBankController extends Controller
{
    private $exambank;
    public function __construct()
    {
        $this->exambank = new exambank();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DB::table('exambank')
            ->select('exambank.*', DB::raw('IF(examshiftdetail.ExamBankId IS NOT NULL, true, false) AS is_exist, IF(criteria.ExamBankId IS NOT NULL, true, false) AS isUse'))
            ->leftJoin('examshiftdetail', 'exambank.ExamBankId', '=', 'examshiftdetail.ExamBankId')
            ->leftJoin('criteria', 'criteria.ExamBankId', '=', 'exambank.ExamBankId')
            ->distinct()
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    public function getCriteriaByExambankId(Request $request)
    {
        try {
            return DB::table('criteria')->where('ExamBankId', '=', $request->ExamBankId)->get();
        } catch (\Throwable $th) {
            \Log::info('ExambankController.getCriteriaByExambankId', [$th]);
        }
    }

    /**
     * Lấy danh sách đề thi đã được thiết lập
     */
    public function getExamBankSetting()
    {
        return DB::table('exambank')
            ->whereIn('ExamBankID', function ($query) {
                $query->select('ExamBankID')
                    ->from('criteria');
            })
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $param = json_decode($request->param, true);
        $validator = Validator::make(
            $param,
            [
                'ExamBankCode' => 'required|unique:exambank,ExamBankCode',
            ],
            [
                'ExamBankCode.unique' => 'Mã đề thi đã tồn tại',
            ]
        );


        if ($validator->fails()) {
            // Xử lý lỗi khi dữ liệu không hợp lệ
            return response()->json([
                'errors' => $validator->errors(),
                'errorCode' => 422,
            ], 500);
        }
        $attributes = $this->readFileData($request);

        exambank::insert($attributes);
    }

    public function deleteCriteria(Request $request)
    {
        return DB::table('criteria')->where('CriteriaId', '=', $request->CriteriaId)->delete();
    }

    public function readFileData(Request $request)
    {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        if (!$receiver->isUploaded()) {
            return response()->json(['error' => 'Đã xảy ra lỗi trong quá trình tải file']);
        }
        $fileReceived = $receiver->receive(); // receive file
        if ($fileReceived->isFinished()) {
            $validator = Validator::make(
                json_decode($request->param, true),
                [
                    'ExamBankCode' => 'required',
                    'ExamBankName' => 'required',
                    'SheetIndexReference' => 'required',
                    'RowReference' => 'required',
                ],
                [
                    'ExamBankCode.required' => 'Mã đề thi không được để trống',
                    'ExamBankName.required' => 'Tên đề thi không được để trống',
                    'SheetIndexReference.required' => 'Sheet tư liệu không được để trống',
                    'RowReference.required' => 'Hàng tiêu đề không được để trống',
                ]
            );


            if ($validator->fails()) {
                // Xử lý lỗi khi dữ liệu không hợp lệ
                return response()->json([
                    'errors' => $validator->errors(),
                    'errorCode' => 422,
                ], 500);
            }

            $attributes = $validator->validated();
            $attributes['FileInfo'] = $request->FileInfo;
            $attributes['FileInfo'] = $request->FileInfo;


            $file = $fileReceived->getFile();
            $ext = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            if ($ext !== 'xlsx') {
                return response()->json(['error' => 'File không đúng định dạng']);
            } else {
                $attributes['ResourcePath'] = "ExcelGrader/ExcelBank/{$attributes['ExamBankCode']}";
                $file->move(public_path() . '/' . $attributes['ResourcePath'], $file->getClientOriginalName());

                $spreadsheet = IOFactory::load($attributes['ResourcePath'] . '/' . $file->getClientOriginalName());


                $selectedSheet = $attributes['SheetIndexReference'];
                $attributes['SheetIndexReference'] = [];
                $attributes['SheetIndexReference'][1] = $selectedSheet;
                $attributes['SheetIndexReference'][0] = [];

                foreach ($spreadsheet->getSheetNames() as $index => $sheetName) {
                    $sheet = [
                        'sheetIndex' => $index,
                        'sheetName' => $sheetName
                    ];
                    $attributes['SheetIndexReference'][0][] = ['sheetIndex' => $index, 'sheetName' => $sheetName];
                }
                $attributes['SheetIndexReference'] = json_encode($attributes['SheetIndexReference']);

                //đọc file excel lấy vùng header
                $dataReference = json_decode($attributes['SheetIndexReference'], true);
                $worksheet = $spreadsheet->getSheet((int) $dataReference[1]);
                //đọc vùng header dữ liệu
                $highestDataColumn = $worksheet->getHighestDataColumn($attributes['RowReference']);
                $rangeHeaders = Coordinate::extractAllCellReferencesInRange("A{$attributes['RowReference']}:{$highestDataColumn}{$attributes['RowReference']}");
                $attributes['DataReference'] = [];
                $attributes['DataReference'][0] = ['SheetIndex' => (int) $dataReference[1], 'RowReference' => $attributes['RowReference']];
                foreach ($rangeHeaders as $range) {
                    $cellValue = $worksheet->getCell($range)->getValue();
                    if ($cellValue !== null) {
                        $coordinateFromString = Coordinate::coordinateFromString($range);
                        $attributes['DataReference'][1][] = ['ColumnName' => $coordinateFromString[0], 'CellAddress' => $range, 'ColumnIndex' => (int) $coordinateFromString[1], 'RowIndex' => $attributes['RowReference'], 'Title' => $cellValue];
                        $attributes['DataReference'][2][] = $cellValue;
                    }
                }
                $attributes['DataReference'] = json_encode($attributes['DataReference']);
                $attributes['ResourceFile'] = $file->getClientOriginalName();
            }
            return $attributes;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $exambank = exambank::find($id)->getAttributes();

        $result = [
            'FontColor' => collect(FontColor::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'Font' => collect(Font::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'FontStyle' => collect(FontStyle::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'FontEffect' => collect(FontEffect::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'FontUnderline' => collect(FontUnderline::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'FormatNumberLabel' => collect(FormatNumber::getInstances())->values()->map(function ($instance) {
                return $instance->description;
            }),
            'FormatNumberValue' => collect(FormatNumber::getInstances())->map(function ($instance) {
                return $instance->value;
            }),
            'FormulaType' => collect(FormulaType::getInstances())
                ->reject(function ($instance) {
                    return in_array($instance->value, [FormulaType::CellReference, FormulaType::StructuredReference]);
                })->values()
                ->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => strtoupper($instance->description),
                    ];
                }),
            'Horizontal' => collect(Horizontal::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'PageOrientation' => collect(PageOrientation::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'Vertical' => collect(Vertical::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'PageSize' => collect(PageSize::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'SheetName' => collect(SheetName::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'TableStyles' => collect(TableStyles::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'TextControl' => collect(TextControl::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'TextDirection' => collect(TextDirection::getInstances())->values()->map(function ($instance) {
                return [
                    'value' => $instance->value,
                    'description' => $instance->description,
                ];
            }),
            'TypeProperty' => collect(TypeProperty::getInstances())->map(function ($instance) {
                return $instance->value;
            }),
        ];
        return $result;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * cập nhật với file tư liệu
     */
    function updateExambank(Request $request)
    {
        //update với file tư liệu
        $param = json_decode($request->param, true);
        //xóa file tư liệu
        $validator = Validator::make(
            $param,
            [
                'ExamBankCode' => "required|unique:exambank,ExamBankCode,{$param['ExamBankId']},ExamBankId",
            ],
            [
                'ExamBankCode.unique' => 'Mã đề thi đã tồn tại',
            ]
        );

        if ($validator->fails()) {
            // Xử lý lỗi khi dữ liệu không hợp lệ
            return response()->json([
                'errors' => $validator->errors(),
                'errorCode' => 422,
            ], 500);
        }
        File::delete(public_path($param['ResourcePath'] . '/' . $param['ResourceFile']));
        $attributes = $this->readFileData($request);
        exambank::find($param['ExamBankId'])->update($attributes);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        //update với file tư liệu
        if (isset($request->File)) {
            $param = json_decode($request->param, true);
            //xóa file tư liệu
            File::delete(public_path($param['ResourcePath'] . '/' . $param['ResourceFile']));
            $validator = Validator::make(
                $param,
                [
                    'ExamBankCode' => "required|unique:exambank,ExamBankCode,{$param['ExamBankId']},ExamBankId",
                ],
                [
                    'ExamBankCode.unique' => 'Mã đề thi đã tồn tại',
                ]
            );

            if ($validator->fails()) {
                // Xử lý lỗi khi dữ liệu không hợp lệ
                return response()->json([
                    'errors' => $validator->errors(),
                    'errorCode' => 422,
                ], 500);
            }
            $attributes = $this->readFileData($request);
            exambank::find($param['ExamBankId'])->update($attributes);
        }
        //update ko có file tư liệu
        else {
            $attributes = $request->validate(
                [
                    'ExamBankCode' => "required|unique:exambank,ExamBankCode,{$request->ExamBankId},ExamBankId",
                    'ExamBankName' => 'required',
                    'SheetIndexReference' => 'required',
                    'RowReference' => 'required',
                ],
                [
                    'ExamBankCode.unique' => 'Mã đề thi đã tồn tại',
                    'ExamBankCode.required' => 'Mã đề thi không được để trống',
                    'ExamBankName.required' => 'Tên đề thi không được để trống',
                    'SheetIndexReference.required' => 'Sheet tư liệu không được để trống',
                    'RowReference.required' => 'Hàng tiêu đề không được để trống',
                ]
            );

            exambank::find($request->ExamBankId)->update($attributes);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        $examShift = DB::table('examshiftdetail')->join('examshift', 'examshift.ExamShiftId', '=', 'examshiftdetail.ExamShiftId')->where('ExamBankId', $id)->first();
        if ($examShift) {
            return response()->json([
                'errors' => "Đề thi đang được sử dụng cho ca thi {$examShift->ExamShiftCode}",
            ], 422);
        }
        $exambank = exambank::find($id);
        File::delete(public_path($exambank->first()->ResourcePath . '/' . $exambank->first()->ResourceFile));
        criteria::where('ExamBankId', $id)->delete();
        $exambank->delete();
        DB::commit();
    }
}
