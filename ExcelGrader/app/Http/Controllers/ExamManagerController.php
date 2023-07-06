<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\exam;
use App\Models\common;
use Carbon\Carbon;
use App\Models\examshift;
use App\Models\examshiftdetail;
use Illuminate\Support\Facades\File;
use App\Enums\FormMode;
use Illuminate\Support\Facades\Validator;
class ExamManagerController extends Controller
{
    private $exam;
    public function __construct(){
        $this->exam = new exam();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DB::select(DB::raw('
        SELECT DISTINCT JSON_ARRAYAGG(JSON_OBJECT("ExamShiftCode",t.ExamShiftCode,"ExamShiftDetailId",t.ExamShiftDetailId,"ExamBankId",t.ExamBankId, "ExamShiftId", t.ExamShiftId,"ResourcePathFileList",t.ResourcePathFileList,"ResourcePathFileAssignment",t.ResourcePathFileAssignment,"ResourcePath",t.ResourcePath,"ExamShiftName", t.ExamShiftName,"ExamBankName",t.ExamBankName,"DateTimeStart",t.DateTimeStart,"DateTimeEnd",t.DateTimeEnd,"DepartmentName",t.DepartmentName,"DepartmentId",t.DepartmentId)) AS ExamShift, MAX(t.ExamId) AS ExamId, t.ExamCode, t.ExamStartDate, t.ExamEndDate, t.Note, t.ExamName
        FROM (
          SELECT DISTINCT e.ExamShiftCode, e.ExamShiftId, e.ExamShiftName, d.DepartmentId, d.DepartmentName, e1.ExamId, e.DateTimeStart, e.DateTimeEnd, e.Note, e1.ExamStartDate, e1.ExamEndDate, e1.ExamCode,e1.ExamName, e2.ResourcePathFileList,e2.ResourcePathFileAssignment,e2.ResourcePath,e3.ExamBankName, e3.ExamBankId,e2.ExamShiftDetailId
          FROM examshift e JOIN exam e1 ON e.ExamId = e1.ExamId JOIN examshiftdetail e2 ON e.ExamShiftId = e2.ExamShiftId JOIN department d ON e2.DepartmentId = d.DepartmentId JOIN exambank e3 ON e2.ExamBankId = e3.ExamBankId
        ) AS t
        GROUP BY t.ExamCode, t.ExamStartDate, t.ExamEndDate, t.Note, t.ExamName;
        '));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    function convertDateTime($date){
        return Carbon::parse($date)->format('Y-m-d h:i:s');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validate dữ liệu
        $attribute = $request->validate([
            'ExamCode' => 'required|unique:exam,ExamCode',
            'ExamName' => 'required',
            'ExamStartDate' => 'required',
            'ExamEndDate' => 'required',
            'Note' => '',
            'listExamShift.*.ExamShiftCode' => 'required|unique:examshift,ExamShiftCode',
            'listExamShift.*.ExamShiftName' => 'required',
            'listExamShift.*.DateTimeStart' => 'required',
            'listExamShift.*.DateTimeEnd' => 'required',
            'listExamShift.*.Department' => 'required',
            'listExamShift.*.ExamBankId' => 'required',
            'listExamShift.*.Note' => '',
        ],
        [
            'ExamCode.required' => 'Mã kì thi không được để trống',
            'ExamCode.unique' => 'Mã kì thi đã tồn tại trong hệ thống',
            'ExamName.required' => 'Tên kì thi không được để trống',
            'ExamStartDate.required' => 'Ngày bắt đầu không được để trống',
            'ExamEndDate.required' => 'Ngày kết thúc không được để trống',
            'listExamShift.*.ExamShiftCode.required' => 'Mã ca thi không được để trống',
            'listExamShift.*.ExamShiftCode.unique' => 'Mã ca thi đã tồn tại trong hệ thống',
            'listExamShift.*.ExamShiftName.required' => 'Tên ca thi không được để trống',
            'listExamShift.*.DateTimeStart.required' => 'Ngày bắt đầu không được để trống',
            'listExamShift.*.DateTimeEnd.required' => 'Ngày kết thúc không được để trống',
            'listExamShift.*.Department.required' => 'Phòng thi không được để trống',
            'listExamShift.*.ExamBankId.required' => 'Đề thi không được để trống',
        ]);

        $attributeExam =  [
            'ExamCode' => $attribute['ExamCode'],
            'ExamName' => $attribute['ExamName'],
            'ExamStartDate' => $this->convertDateTime($attribute['ExamStartDate']),
            'ExamEndDate' => $this->convertDateTime($attribute['ExamEndDate']),
            'Note' => $attribute['Note'],
        ];
        try {
            DB::beginTransaction();
            $examId =  exam::insertGetId($attributeExam);
            $listExamShiftDetail = [];
            $attributeExamShift = [];
            foreach ($attribute['listExamShift'] as $key => $examShift) {
                $attributeExamShift[] = [
                    'ExamShiftCode' => $examShift['ExamShiftCode'],
                    'ExamShiftName' => $examShift['ExamShiftName'],
                    'DateTimeStart' => $this->convertDateTime($examShift['DateTimeStart']),
                    'DateTimeEnd' => $this->convertDateTime($examShift['DateTimeEnd']),
                    'ExamId' => $examId,
                ];
                foreach ($examShift['Department'] as $departmantId) {
                    $objExamShiftDetail['DepartmentId'] = $departmantId;
                    //chi tiết đề
                    foreach ($examShift['ExamBankId'] as $examBankId) {
                        $objExamShiftDetail['ExamBankId'] = $examBankId;
                        $listExamShiftDetail[$key][] = $objExamShiftDetail;
                    }
                }
            }
            //thêm mới ca thi
            examshift::insert($attributeExamShift);
            $startID = DB::select('select last_insert_id() as id');
            $startID = $startID[0]->id; // This will return 601
            $lastID = $startID + count($attribute['listExamShift']) - 1; // this will return 603
            $index = 0;
            $attributeExamShiftDetail = [];
            //thêm mã ca thi vào chi tiết ca thi
            for ($i= $startID; $i <=$lastID ; $i++) {
                foreach ($listExamShiftDetail[$index] as $key => $examShiftDetail) {
                    $examShiftDetail['ExamShiftId'] = $i;
                    $attributeExamShiftDetail[] = $examShiftDetail;
                }
                $index++;
            }
            examshiftdetail::insert($attributeExamShiftDetail);
            DB::commit();
        } catch (\Throwable $th) {
            return ['errors' => 'Có lỗi xảy ra'];
        }

    }

    public function getExamShift($id){
        $sql = " SELECT t.ExamShiftId,t.ExamShiftCode,t.ExamShiftName,t.DateTimeStart,t.DateTimeEnd,t.Note, JSON_ARRAYAGG(JSON_OBJECT('DepartmentName',t.DepartmentName,'DepartmentId',t.DepartmentId,'ExamBankId',t.ExamBankId,'ExamBankName',t.ExamBankName)) as objDE
        FROM (
          SELECT DISTINCT e.ExamShiftCode, e.ExamShiftId, e.ExamShiftName, d.DepartmentId, d.DepartmentName, e.DateTimeStart, e.DateTimeEnd, e.Note, e2.ResourcePathFileList,e2.ResourcePathFileAssignment,e2.ResourcePath,e3.ExamBankName, e3.ExamBankId
          FROM examshift e JOIN examshiftdetail e2 ON e.ExamShiftId = e2.ExamShiftId JOIN department d ON e2.DepartmentId = d.DepartmentId JOIN exambank e3 ON e2.ExamBankId = e3.ExamBankId WHERE e.ExamId IN ({$id})
        ) AS t
        GROUP BY t.ExamShiftId,t.ExamShiftCode,t.ExamShiftName,t.DateTimeStart,t.DateTimeEnd,t.Note";
        return DB::select(DB::raw($sql));

        // return DB::table('examshift')->whereIn('ExamId', [$id])->get();
    }

    /**
     * Cập nhật kì thi
     */
    public function updateExamManager(Request $request){
         //validate dữ liệu
         $attribute = $request->validate([
            'ExamCode' => "required|unique:exam,ExamCode,{$request->ExamId},ExamId",
            'ExamName' => 'required',
            'ExamStartDate' => 'required',
            'ExamEndDate' => 'required',
            'Note' => '',
        ],
        [
            'ExamCode.required' => 'Mã kì thi không được để trống',
            'ExamCode.unique' => 'Mã kì thi đã tồn tại trong hệ thống',
            'ExamName.required' => 'Tên kì thi không được để trống',
            'ExamStartDate.required' => 'Ngày bắt đầu không được để trống',
            'ExamEndDate.required' => 'Ngày kết thúc không được để trống',
        ]);

        $attribute['ExamStartDate'] = $this->convertDateTime($attribute['ExamStartDate']);
        $attribute['ExamEndDate'] = $this->convertDateTime($attribute['ExamEndDate']);
        exam::find($request->ExamId)->update($attribute);

    }

    /**
     * Kiểm tra sự tồn tại của mã ca thi
     * @return bool true: tồn tại | false: ko tồn tại
     */
    public function checkExamShiftCodeExits(Request $request){
        //insert
        if($request->mode == 1){
            // validate incoming request
            $validator = Validator::make($request->data, [
                'ExamShiftCode' => 'unique:examshift,ExamShiftCode',
            ]);
        }
        //update
        else if($request->mode == 2){
            $validator = Validator::make($request->data, [
                'ExamShiftCode' => "required|unique:examshift,ExamShiftCode,{$request->data['ExamShiftId']},ExamShiftId",
            ]);
        }

        if ($validator->fails()) {
            return ['result' => true];
        }
        return ['result' => false];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Xử lý thêm mới/cập nhật ca thi
     */
    function handlerExamShift(Request $request,$mode){
        switch ($mode) {
            case FormMode::Insert:
                $validate = "required|unique:examshift,ExamShiftCode";
                break;
            case FormMode::Update:
                $validate = "required|unique:examshift,ExamShiftCode,{$request['ExamShiftId']},ExamShiftId";
                break;
            default:
                # code...
                break;
        }
        $attribute = $request->validate([
            'ExamShiftCode' => $validate,
            'ExamShiftName' => 'required',
            'ExamBankId' => 'required',
            'Department' => 'required',
            'DateTimeStart' => 'required',
            'DateTimeEnd' => 'required',
            'Note' => '',
            'ExamId' => 'required',
        ],
        [
            'ExamShiftCode.required' => 'Mã ca thi không được để trống',
            'ExamShiftCode.unique' => 'Mã ca thi đã tồn tại trong hệ thống',
            'ExamShiftName.required' => 'Tên ca thi không được để trống',
            'DateTimeStart.required' => 'Ngày bắt đầu không được để trống',
            'DateTimeEnd.required' => 'Ngày kết thúc không được để trống',
            'Department.required' => 'Phòng thi không được để trống',
            'ExamBankId.required' => 'Đề thi không được để trống',
        ]);

        $attributeExamShift = [
            'ExamShiftCode' => $attribute['ExamShiftCode'],
            'ExamShiftName' => $attribute['ExamShiftName'],
            'DateTimeStart' => $this->convertDateTime($attribute['DateTimeStart']),
            'DateTimeEnd' => $this->convertDateTime($attribute['DateTimeEnd']),
            'ExamId' => $attribute['ExamId'],
        ];

        $attributeExamShiftDetail = [];
        foreach ($attribute['Department'] as $departmantId) {
            $objExamShiftDetail['DepartmentId'] = $departmantId;
            //chi tiết đề
            foreach ($attribute['ExamBankId'] as $examBankId) {
                $objExamShiftDetail['ExamBankId'] = $examBankId;
                $attributeExamShiftDetail[] = $objExamShiftDetail;
            }
        }
        try {
            DB::transaction(function () use($attributeExamShift,$attributeExamShiftDetail,$request,$mode) {
                if($mode == FormMode::Update){
                    //có sự thay đổi đề thi hoặc phòng thi
                    if($request->Flag){
                        $examShiftDetail = examshiftdetail::whereIn('ExamShiftId', [$request->ExamShiftId]);
                        $examResult = DB::table('examresult')->whereIn('ExamShiftDetailId', $examShiftDetail->pluck('ExamShiftDetailId')->toArray());
                        $examResultDetail = DB::table('examresult')->whereIn('ExamShiftDetailId', $examShiftDetail->pluck('ExamShiftDetailId')->toArray());
                        //xóa kết quả chi tiết của bài thi
                        $examResultDetail->delete();
                        //xóa kết quả chi của bài thi
                        $examResult->delete();
                        //xóa các ca thi chi tiết trong kì thi
                        $examShiftDetail->delete();
                    }
                    examshift::find($request->ExamShiftId)->update($attributeExamShift);
                    $examShiftId = $request->ExamShiftId;
                }
                if($mode == FormMode::Insert){
                    //thêm mới ca thi
                    $examShiftId =  examshift::insertGetId($attributeExamShift);
                }

                foreach ($attributeExamShiftDetail as $key => $value) {
                    $attributeExamShiftDetail[$key]['ExamShiftId'] = $examShiftId;
                }
                examshiftdetail::insert($attributeExamShiftDetail);

            });
        } catch (\Exception $e) {
            dd($e);
            \Log::debug($e->getMessage());
        }

    }

    //Thêm mới ca thi
    public function insertExamShift(Request $request){
        $this->handlerExamShift($request,FormMode::Insert);
    }

    /**
     * Xóa ca thi
     */
    public function deleteExamShift($id){

        try {
            DB::transaction(function () use($id) {
                $examShiftDetail = examshiftdetail::whereIn('ExamShiftId', [$id]);
                $examResult = DB::table('examresult')->whereIn('ExamShiftDetailId', $examShiftDetail->pluck('ExamShiftDetailId')->toArray());
                $examResultDetail = DB::table('examresult')->whereIn('ExamShiftDetailId', $examShiftDetail->pluck('ExamShiftDetailId')->toArray());
                examshift::find($id)->delete();
            });
        } catch (\Exception $e) {
            dd($e);
            \Log::debug($e->getMessage());
        }
    }

    /**
     * cập nhật ca thi
     */
    public function updateExamShift(Request $request){
        $this->handlerExamShift($request,FormMode::Update);
    }

    function array_equal($a, $b) {
        return (
             is_array($a)
             && is_array($b)
             && count($a) == count($b)
             && array_diff($a, $b) === array_diff($b, $a)
        );
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            $examShift = DB::table('examshift')->whereIn('ExamId', [$id]);
            $examShiftDetail = DB::table('examShiftDetail')->whereIn('ExamShiftId', $examShift->pluck('ExamShiftId')->toArray());
            $examResult = DB::table('examresult')->whereIn('ExamShiftDetailId', $examShiftDetail->pluck('ExamShiftDetailId')->toArray());
            $examResultDetail = DB::table('examresult')->whereIn('ExamShiftDetailId', $examShiftDetail->pluck('ExamShiftDetailId')->toArray());
            DB::beginTransaction();
            //xóa kết quả chi tiết của bài thi
            $examResultDetail->delete();
            //xóa kết quả chi của bài thi
            $examResult->delete();
            //xóa các ca thi chi tiết trong kì thi
            $examShiftDetail->delete();
            //xóa ca thi
            $examShift->delete();
            //xóa kì thi
            exam::find($id)->delete();
            //xóa thư mục lưu kết quả của kì thi
            File::deleteDirectory(common::DIR_EXAMSCORE."/{$id}");
            DB::commit();
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }
}
