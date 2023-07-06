<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Calculation\Engine\Operands\StructuredReference;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Illuminate\Support\Facades\File;
use App\Models\criteria;
use App\Models\examresult;
use App\Models\examresultdetail;

use App\Enums\FormulaType;
use App\Enums\TypeProperty;
use App\Enums\TextControl;
use App\Enums\FontColor;
use App\Enums\Font;
use App\Enums\SheetName;
use App\Enums\Vertical;
use App\Enums\FileData;
use App\Enums\FontColorDefault;
use App\Enums\FormatNumberAccounting;
use App\Enums\FormatNumber;
class PhpSpreadsheetController extends Controller
{
    protected $spreadsheet;
    protected $list = [];
    protected $deviation = []; //Mảng lưu các địa chỉ bị lệch
    protected $isDeviation = false; //biến kiểm tra có bị lệch vùng ko
    //Loại chấm điểm: false: tuyệt đối, true: tương đối
    protected $gradingType = false;
    //điểm bị trừ nếu có lệch vùng
    protected $deductedPoint = 0;
    protected $sheetCount = 0;  //số lượng sheet trong bài thi
    protected $listTitleSheet = [];
    protected $listFormula = [];
    private const BINARY_OPERATORS = [
        '+' => true, '-' => true, '*' => true, '/' => true,
        '^' => true, '&' => true, '>' => true, '<' => true,
        '=' => true, '>=' => true, '<=' => true, '<>' => true,
        '∩' => true, '∪' => true, ':' => true,
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->deductedPoint = (1 - ($request->deductedPoint / 100));
        $this->gradingType = $request['gradingType'];
        $data = $request['data'][0];
        $path = $data['ResourcePath'];
        $path_danh_sach_excel = $data['ResourcePathFileList'];
        $path_bai_thi =  $data['ResourcePathFileAssignment'];
        $zip = new ZipArchive;
        $res = $zip->open($path.'/'.$path_bai_thi);
        if ($res === TRUE) {
            // Extract file
            $zip->extractTo($path.'/Exam');
            $zip->close();
          } else {
            echo '<br><font color=red><b>failed!</b></font>';
        }
        $dirParent = $path.'/Exam';
        $handleParent = opendir($dirParent);
        $valid_xlsx = array('xlsx');
        $valid_docx = array('docx');
        $valid_pptx = array('pptx');

        $pathTo = "E";
        File::deleteDirectory($path.'/Copy/E');
        File::deleteDirectory($path.'/Copy/P');
        File::deleteDirectory($path.'/Copy/W');
        mkdir($path.'/Copy/E',0777,true);  //tạo các thư mục lưu trữ bài
        mkdir($path.'/Copy/P',0777,true);
        mkdir($path.'/Copy/W',0777,true);
        $students = [];
        try {
            if($handleParent){
                //đọc folder bài tải lên
                while (($fileParent = readdir($handleParent)) !== FALSE) {
                    if (!in_array($fileParent, array('.', '..')) && !is_dir($dirParent.$fileParent)){
                        //đọc từng folder con để lấy ra bài word | excel | pp tương ứng
                        $dirChild = $dirParent.'/'.$fileParent;
                        $parts = explode('_',$fileParent);
                        $studentID = $parts[0];                                             //mã sinh viên
                        // $infos = explode(' ',explode('-',$parts[1])[1]);
                        // $key_student =  $infos[0];                                          //Khóa
                        // $studentDepartment = $infos[1];                                     //Khoa
                        //thông tin sinh viên
                        $students[$studentID] = ['studentID'=> $studentID];
                        if ($handleChild = opendir($dirChild)) {
                            while (($fileChild = readdir($handleChild)) !== FALSE) {
                                if (!in_array($fileChild, array('.', '..')) && !is_dir($dirChild.$fileChild)){
                                    $ext = strtolower(pathinfo($fileChild, PATHINFO_EXTENSION));
                                    if($pathTo === "E"){
                                        if(in_array(strtolower($ext),$valid_xlsx)){

                                            $students[$studentID]['studentAssignment'][] = $fileChild;  //tên file
                                            $students[$studentID]['path'] =  $dirParent.'/'.$fileParent;  //đường dẫn đến bài
                                            // // copy($dirChild.'/'.$fileChild,$path.'/Copy/E/'.($key).'-'.$fileChild);
                                        }
                                    }
                                    if($pathTo === "P"){
                                        if(in_array(strtolower($ext),$valid_pptx)){
                                            // copy($dirChild.'/'.$fileChild,$path.'/Copy/P/'.($key).'-'.$fileChild);
                                        }
                                    }
                                    if($pathTo === "W"){
                                        if(in_array(strtolower($ext),$valid_pptx)){
                                            // copy($dirChild.'/'.$fileChild,$path.'/Copy/W/'.($key).'-'.$fileChild);
                                        }
                                    }
                                }
                            }
                            closedir($handleChild);
                        }
                    }
                }
                closedir($handleParent);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        $this->spreadsheet = IOFactory::load($path.'/'.$path_danh_sach_excel);


        //sắp xếp thứ tự đề thi theo id
        $examBank = $request['data'];
        usort($examBank, function($a, $b) {
            return $a['ExamBankId'] > $b['ExamBankId'];
        });
        //loại bỏ đề trùng
        $examBank = array_intersect_key($examBank, array_unique(array_column($examBank, 'ExamBankId')));
        //đánh lại key
        $examBank = array_values($examBank);

        $countExam = count($examBank);

        //tạo các folder đề thi tương ứng
        foreach ($examBank as $key => $item) {
            $examBank[$key]['FolderName'] = "{$item['ExamBankName']}-{$item['ExamBankId']}";
            $examBank[$key]['Path'] = "{$path}/Copy/E/{$item['ExamBankName']}-{$item['ExamBankId']}";
            $examBank[$key]['ID'] = $item['ExamBankId'];
            $examBank[$key]['Name'] = $item['ExamBankName'];
            is_dir("{$path}/Copy/E/{$examBank[$key]['FolderName']}") ?: mkdir("{$path}/Copy/E/{$examBank[$key]['FolderName']}",0777,true);
        }

        for ($i= 2; $i <= 50 ; $i++) {
            $exam_number_list[] = [(int)$this->spreadsheet->getActiveSheet()->getCell('A'.$i)->getValue(),(int)$this->spreadsheet->getActiveSheet()->getCell('B'.$i)->getValue()];  //danh sách số báo danh, mã sinh viên

            $studentID = trim($this->spreadsheet->getActiveSheet()->getCell('B'.$i)->getValue());    //mã sinh viên
            $keyStudent = trim($this->spreadsheet->getActiveSheet()->getCell('A'.$i)->getValue());   //số báo danh
            $studentName = trim($this->spreadsheet->getActiveSheet()->getCell('C'.$i)->getValue());
            //kiểm tra mã trong mảng bài thi đã nộp
            if(array_key_exists($studentID,$students) && isset($students[$studentID]['path'])){
                $dir = $students[$studentID]['path'];
                $handle = opendir($dir);
                while (($file = readdir($handle)) !== FALSE) {
                    //đọc folder của ng thi di chuyển vào folder đề tương ứng
                    foreach ($students[$studentID]['studentAssignment'] as $key => $itemFile) {
                        if($file === $itemFile){
                            $index = $keyStudent % $countExam;
                            $students[$studentID]['studentName'] = $studentName;
                            $students[$studentID]['examId'] = $examBank[$index]['ID'];
                            copy("{$students[$studentID]['path']}/{$itemFile}","{$path}/Copy/E/{$examBank[$index]['FolderName']}/{$studentID}-{$students[$studentID]['studentAssignment'][$key]}");



                            // //số lượng đề thi sử dụng
                            // switch ((int)$countExam) {
                            //     //sử dụng 1 đề
                            //     case 1:
                            //         $students[$studentID]['studentName'] = $studentName;
                            //         $students[$studentID]['examId'] = $examBank[0]['ID'];
                            //         copy("{$students[$studentID]['path']}/{$itemFile}","{$path}/Copy/E/{$examBank[0]['FolderName']}/{$studentID}-{$students[$studentID]['studentAssignment'][$key]}");
                            //         break;
                            //     //sử dụng 2 đề
                            //     case 2:
                            //         if($keyStudent % 2 != 0){
                            //             $this->spreadsheet->getActiveSheet()->getCell('O'.$i)->setValue($examBank[1]['Name']);
                            //             $this->spreadsheet->getActiveSheet()->getCell('P'.$i)->setValue($studentID.'-'.$students[$studentID]['studentAssignment'][$key]);
                            //             $students[$studentID]['studentName'] = $studentName;
                            //             $students[$studentID]['examId'] = $examBank[1]['ID'];
                            //             copy("{$students[$studentID]['path']}/{$itemFile}","{$path}/Copy/E/{$examBank[1]['FolderName']}/{$studentID}-{$students[$studentID]['studentAssignment'][$key]}");
                            //         }
                            //         else{
                            //             $this->spreadsheet->getActiveSheet()->getCell('O'.$i)->setValue($examBank[0]['Name']);
                            //             $this->spreadsheet->getActiveSheet()->getCell('P'.$i)->setValue($studentID.'-'.$students[$studentID]['studentAssignment'][$key]);
                            //             $students[$studentID]['studentName'] = $studentName;
                            //             $students[$studentID]['examId'] = $examBank[0]['ID'];
                            //             copy("{$students[$studentID]['path']}/{$itemFile}","{$path}/Copy/E/{$examBank[0]['FolderName']}/{$studentID}-{$students[$studentID]['studentAssignment'][$key]}");
                            //         }
                            //         break;
                            //     //sử dụng 3 đề
                            //     case 3:

                            //         break;
                            //     default:
                            //         # code...
                            //         break;
                            // }

                            $students[$studentID]['studentOrder'] = $keyStudent;
                        }
                    }
                }
            }
            else if(is_numeric(trim($studentID)) != "" && is_numeric(trim($keyStudent)) != "" && trim($studentName) != ""){
                $students[$studentID]['studentID'] = $studentID;
                $students[$studentID]['studentName'] = $studentName;
                $students[$studentID]['studentOrder'] = $keyStudent;
                $students[$studentID]['note'] = 'Không có bài hoặc nộp file giải nén';
            }
        }

        //xóa thông tin bài thi nếu đã có trước đó
        // DB::table('bai_thi')->join('chi_tiet_bai_thi','bai_thi.StudentCode','=','chi_tiet_bai_thi.StudentCode')->where('bai_thi.id_chi_tiet_ca_thi',$request->id)->delete();
        // $j = 1;

        try {
            foreach ($examBank as $key => $item) {
                DB::beginTransaction();
                DB::table('examresult')->where('ExamShiftDetailId',$examBank[$key]['ExamShiftDetailId'])->delete();
                DB::table('examresultdetail')->where('ExamShiftDetailId',$examBank[$key]['ExamShiftDetailId'])->delete();
                $this->gradeExam($examBank[$key],$examBank[$key]['Path'],$students);
                DB::commit();
            }
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th]);
        }

    }

    public function gradeExam($param,$path,$students){
        $listExam = []; //danh sách thông tin các bài thi
        $listExamDetail = [];  //danh sách chi tiết về bài thi
        //object bài thi
        $itemExam = [
            'StudentCode' => '',
            'CandidateNumber' => '', //số báo danh
            'ExamShiftDetailId' => $param['ExamShiftDetailId'],
            'TotalScore' => 0,
            'StudentName' => '',
            'DepartmentName' => $param['DepartmentName'],
            'Path' => '',
            'Note' => '',
        ];
        $tieu_chi = DB::table('criteria')->where('ExamBankId',$param['ID'])->get();
        //file tư ID
        $fileData = DB::table('exambank')->where('ExamBankId',$param['ID'])->get()[0];
        //vùng header tự liệu
        $fileDataReference = json_decode($fileData->DataReference);
        //độ lệch vùng dữ liệu so với tư liệu
        $regionDeviation = [];
        // if($id_de_thi==2){
        //     dd($fileData);
        // }

        $dir = $path;
        // $this->readFile();
        if ($handle = opendir($dir)) {
            //duyệt từng file trong thư mục exam
            while (($file = readdir($handle)) !== false){
                if (!in_array($file, array('.', '..')) && !is_dir($dir.$file)){
                    //đọc file excel
                    $this->spreadsheet = IOFactory::load($dir.'/'.$file,IReader::LOAD_WITH_CHARTS);
                    $this->spreadsheet->setActiveSheetIndex(0);
                    $arrName = [];  //mảng lưu name range
                    // dd($this->spreadsheet->getSheet(1)->getCell('731!F32'));
                    foreach ($this->spreadsheet->getDefinedNames() as $key => $value) {
                        //bỏ qua các vùng lọc
                        if(str_contains($key,'XLNM.CRITERIA') || str_contains($key,'XLNM.EXTRACT')){
                            $title = $value->getWorksheet()->getTitle();
                            $objName['subName'] = $value->getName();
                            $objName['range']= str_replace('$','',$value->getValue());
                            $objName['name'] = $key;
                            $objName['sheet_index'] =$this->spreadsheet->getIndex($value->getWorksheet());
                            array_push($arrName,$objName);
                        }
                    }
                    //lấy ra các table có trong bài thi
                    $arrTable = [];
                    $sheets = []; //mảng lưu thông tin các sheet
                    $sheetDatas = []; //Mảng lưu thông tin các data của từng sheet
                    //Mảng lưu các công thức có trong bài thi
                    $this->listFormula = [];
                    $this->sheetCount = $this->spreadsheet->getSheetCount();
                    $this->listTitleSheet = [];
                    for ($i=0; $i <$this->sheetCount ; $i++) {
                        $title = $this->spreadsheet->getSheet($i)->getTitle();
                        $this->listTitleSheet[$i] = $title;
                        //lấy ra thông tin về sheet
                        $objSheet['SheetName'] = $title;
                        $objSheet['SheetIndex'] = $i;
                        array_push($sheets,$objSheet);

                        //lấy ra thông tin các table trong sheet
                        foreach ($this->spreadsheet->getSheet($i)->getTableCollection() as $table) {
                            $table->setShowHeaderRow(true);
                            $objTable['TableName'] = $table->getName();
                            $objTable['TableRange'] = $table->getRange();
                            $objTable['SheetIndex'] = $i;
                            [$rangeStart, $rangeEnd] = Coordinate::rangeBoundaries($table->getRange());
                            $objTable['RowStart'] = $rangeStart[1];
                            $objTable['RowEnd'] = $rangeEnd[1];
                            $objTable['ColumnStart'] = $rangeStart[0];
                            $objTable['ColumnEnd'] = $rangeEnd[0];
                            $header = [];
                            //duyệt toàn bộ header của bảng để lấy thông tin
                            for ($col=$rangeStart[0]; $col <=$rangeEnd[0] ; $col++) {
                                //duyệt header để lấy thông tin
                                $headerTable = new class{};
                                $cellHeader = $this->spreadsheet->getSheet($i)->getCell([$col,$rangeStart[1]]); //lấy ra cell header
                                $headerTable->Field = $cellHeader->getValue();
                                $headerTable->ColumnIndex = $col;
                                $headerTable->ColumnName = $cellHeader->getColumn();
                                $header[$cellHeader->getValue()] = $headerTable;
                                // $headerTable[$cell->getValue()]= $header;
                                // array_push($header,$headerTable);
                            }
                            $objTable['Column'] = $header;
                            $arrTable[$table->getName()]=$objTable;
                        }
                         //đọc data của sheet lưu vào 1 mảng
                         $sheetDatas[$i] = $this->spreadsheet->getSheet($i)->toArray(null, false, false, true);
                    }

                    //mảng lưu độ lệch vị trí
                    $this->deviation = [];
                    foreach ($sheetDatas[0] as $key => $rowDatas) {
                        if($this->handleDeviation($fileDataReference,$rowDatas,$key,$this->deviation)){
                            break;
                        }
                    }
                    $this->handlerFormulaAddress($sheetDatas,$arrTable);

                    //khởi tạo mảng lưu thông tin về các tiêu chí của 1 bài thi
                    $criteriaList = [];

                    //class lưu thông tin tiêu chí mỗi lần duyệt
                    $criteria = [
                        'PropertyName'=>'',
                        'StudentCode' => '',
                        'Point'=>0,
                        'ExamBankName' => $param['ExamBankName'],
                        'DepartmentName'=>  $param['DepartmentName'],
                        'ExamShiftName'=>  $param['ExamShiftName'],
                        'ExamShiftDetailId'=>  $param['ExamShiftDetailId'],
                        'CandidateNumber'=> $students[substr($file,0,9)]['studentOrder'] ?? '',
                        'RealScore' => 0,
                        'Flag' =>true,
                    ];

                    //mã sinh viên trong tên folder
                    $studentIDExam = substr($file,0,9);

                    $totalScore = 0;
                    //duyệt từng tiêu chí trong db
                    foreach($tieu_chi as $key=>$itemCriteria){
                        $criteria['Point'] = $itemCriteria->Point;
                        $criteria['RealScore'] = $itemCriteria->Point;
                        $criteria['PropertyName'] = $itemCriteria->PropertyName;
                        $criteria['Flag'] = true;
                        $criteria['StudentCode'] = substr($file,0,9);
                        switch($itemCriteria->TypeProperty){
                            case TypeProperty::Font:
                                if(strpos($itemCriteria->Range,'XFD1') !== false){
                                    if(!$this->verifyAllFont($itemCriteria)){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        $criteria['PropertyName'] = 'Font All';
                                    }
                                }
                                else{
                                    if(!$this->verifyFont($itemCriteria)){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                    }
                                }
                                break;
                            case TypeProperty::FontStyle:
                                if(!$this->FontStyle($itemCriteria->SheetId,$itemCriteria->Range,$itemCriteria->Content)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::FontSize:
                                if(strpos($itemCriteria->Range,'XFD1') !== false){
                                    if(!$this->verifyAllFontSize($itemCriteria)){
                                        // if($file == '725620001-15-Vũ Nhữ Diên An-501.xlsx'){
                                        //     dd($this->list);
                                        // }
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        $criteria['PropertyName'] = 'Font Size All';
                                    }
                                }
                                else{
                                    if(!$this->verifyFontSize($itemCriteria)){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                    }
                                }
                                break;
                            case TypeProperty::FontUnderline:
                                break;
                            case TypeProperty::FontColor:
                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                //tiêu đề sheet
                                $title = $this->spreadsheet->getSheet($itemCriteria->SheetId)->getTitle();
                                //vùng cần kiểm tra color
                                $arrRange = Coordinate::extractAllCellReferencesInRange($title.'!'.$itemCriteria->Range);
                                $COLOR = FontColor::getValue($itemCriteria->Content);
                                $worksheet = $this->spreadsheet->getSheet($itemCriteria->SheetId);
                                foreach ($arrRange as $key => $range) {
                                    $color = $worksheet->getCell($range)->getStyle()->getFont()->getColor()->getArgb();

                                    if($color != $COLOR){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        break;
                                    }
                                }
                                break;
                            case TypeProperty::FontEffect:
                                break;
                            case TypeProperty::Horizontal:
                                $func = \App\Enums\Horizontal::getKey((int)$itemCriteria->Content);
                                if($this->$func($itemCriteria->SheetId,$itemCriteria->Range,$itemCriteria->Range)){
                                    $criteria['Flag'] = true;
                                }
                                else{
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::Vertical:
                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }

                                $worksheet = $this->spreadsheet->getSheet($itemCriteria->SheetId);


                                switch ($itemCriteria->Content) {
                                    case Vertical::Top:

                                        break;
                                    case Vertical::Center:
                                        try {
                                            if($this->spreadsheet->getSheet($itemCriteria->SheetId)->getStyle($itemCriteria->Range)->getAlignment()->getVertical() !== 'center'){
                                                $criteria['Flag'] = false;
                                                $criteria['RealScore'] = 0;
                                                break;
                                            }
                                        } catch (\Throwable $th) {
                                        }
                                        break;
                                    case Vertical::Bottom:
                                        if($this->spreadsheet->getSheet($itemCriteria->SheetId)->getStyle($range)->getAlignment()->getVertical() !== 'bottom'){
                                            $criteria['Flag'] = false;
                                            $criteria['RealScore'] = 0;
                                        }
                                        break;
                                    case Vertical::Justify:
                                        if($this->spreadsheet->getSheet($itemCriteria->SheetId)->getStyle($range)->getAlignment()->getVertical() !== 'justify'){
                                            $criteria['Flag'] = false;
                                            $criteria['RealScore'] = 0;
                                        }
                                        break;
                                    case Vertical::Distrubuted:

                                        break;
                                    default:
                                        # code...
                                        break;
                                }

                                break;
                            case TypeProperty::Indent:
                                break;
                            case TypeProperty::TextControl:
                                $functionName = TextControl::getKey((int)$itemCriteria->Content);
                                if($this->$functionName($itemCriteria->SheetId,$itemCriteria->Range)){
                                    $criteria['Flag'] = true;
                                }
                                else{
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::TextDirection:
                                break;
                            case TypeProperty::RowHeight:

                                break;
                            case TypeProperty::FileName:
                                if($this->stripUnicode(substr($file,strpos($file,'-') + 1)) !== $this->stripUnicode($itemCriteria->Content)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::Info:
                                $info = $this->spreadsheet->getProperties()->getCreator();
                                try {
                                    if(!str_contains($this->stripUnicode($info),$this->stripUnicode($students[$studentIDExam]['studentName']))){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        break;
                                    }
                                } catch (\Throwable $th) {
                                    dd($studentIDExam ,$students);
                                }
                                break;
                            case TypeProperty::ColumnWidth:
                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                $arrColumn = explode(',',$itemCriteria->Range);
                                foreach ($arrColumn as $key => $column) {
                                    $arrRange = Coordinate::extractAllCellReferencesInRange($column);
                                    foreach ($arrRange as $columnItem) {
                                        $column = preg_replace('/\d/', '', $columnItem);
                                    }
                                    $columnWidth = bcdiv($this->spreadsheet->getSheet($itemCriteria->SheetId)->getColumnDimension($column)->getWidth()-0.71, 1, 2);
                                    if($itemCriteria->Content != $columnWidth){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        break;
                                    }
                                }
                                break;
                            case TypeProperty::TableName:
                                //kiểm tra sự tồn tại của sheet trong bài thi
                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }

                                 //vùng table
                                 $range = $itemCriteria->Range.':'.$itemCriteria->Range;
                                 $rangeBoundaries = Coordinate::rangeBoundaries($range);

                                 //tìm kiếm trong mảng table đã đọc từ bài thi và kiểm tra name
                                 $table = array_filter($arrTable,function($table) use($rangeBoundaries,$itemCriteria){
                                     return $table['SheetIndex'] == $itemCriteria->SheetId && $table['ColumnStart'] <= $rangeBoundaries[0][0] && $table['RowStart'] <= (int)$rangeBoundaries[0][1] && $table['ColumnEnd'] >= $rangeBoundaries[1][0]
                                         && $table['RowEnd'] >= (int)$rangeBoundaries[1][1] && $this->stripUnicode($itemCriteria->Content) === $this->stripUnicode($table['TableName']);
                                 });

                                 if(!$table){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                 }
                                break;
                            case TypeProperty::TableStyle:

                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                //vùng table
                                $range = $itemCriteria->Range.':'.$itemCriteria->Range;
                                $rangeBoundaries = Coordinate::rangeBoundaries($range);

                                //tìm kiếm trong mảng table đã đọc từ bài thi
                                $table = array_filter($arrTable,function($table) use($rangeBoundaries,$itemCriteria){
                                    return $table['SheetIndex'] == $itemCriteria->SheetId && $table['ColumnStart'] <= $rangeBoundaries[0][0] && $table['RowStart'] <= (int)$rangeBoundaries[0][1] && $table['ColumnEnd'] >= $rangeBoundaries[1][0]
                                        && $table['RowEnd'] >= (int)$rangeBoundaries[1][1];
                                });


                                //nếu có table thì tiến hành kiểm tra style
                                if($table){
                                    if($this->spreadsheet->getSheet($itemCriteria->SheetId)->getTableByName(end($table)['TableName'])->getStyle()->getTheme() !==\App\Enums\TableStyles::getKey((int)$itemCriteria->Content)){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        break;
                                    }
                                }
                                else{
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }

                                break;
                            case TypeProperty::PageSize:
                                if(!$this->PageSize($itemCriteria->SheetId,$itemCriteria->Content)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                break;
                            case TypeProperty::PageOrientation:
                                $func = TypeProperty::getKey($itemCriteria->TypeProperty);
                                // $criteria['PropertyName'] = \App\Enums\PageOrientation::getKey((int)$itemCriteria->Content);
                                if($this->$func($itemCriteria->SheetId,$itemCriteria->Content)){
                                    $criteria['Flag'] = true;
                                }
                                else{
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::Text:
                                if($this->sheetExists($itemCriteria->SheetId)){
                                    //xóa toàn bộ dấu câu và khoảng cách nếu có rồi tiền hành so sánh
                                    if($this->stripUnicode(str_replace(' ','',mb_strtolower($this->spreadsheet->getSheet($itemCriteria->SheetId)->getCell($itemCriteria->Range)->getValue()))) !== $this->stripUnicode(str_replace(' ','',mb_strtolower($itemCriteria->Content)))){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        break;
                                    }
                                }
                                else{
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::AlignOrientation:
                                break;
                            case TypeProperty::FormatNumber:
                                $content = json_decode($itemCriteria->Content);
                                switch ($content->type) {
                                    case FormatNumber::Number:
                                        # code...
                                        break;
                                    case FormatNumber::Currency:
                                        break;
                                    case FormatNumber::Accounting:
                                        //lấy ra giá trị value tương ứng với tên enum
                                        $FORMAT_ACCOUNTING = FormatNumberAccounting::getValue($content->symbol);
                                        //thêm số lượng 0.00 tùy theo số lượng decimal
                                        $FORMAT_ACCOUNTING = str_replace("#0",'#'.number_format(0,$content->decimal),$FORMAT_ACCOUNTING);
                                        //nếu sheet ko tồn tại thì tiêu chí false
                                        if(!$this->sheetExists($itemCriteria->SheetId)){
                                            $criteria['Flag'] = false;
                                            $criteria['RealScore'] = 0;
                                            break;
                                        }
                                        $title = $this->spreadsheet->getSheet($itemCriteria->SheetId)->getTitle();
                                        $arrRange =  Coordinate::extractAllCellReferencesInRange("'".$title."'!".$itemCriteria->Range);

                                        //kiểm tra vùng
                                        $worksheet = $this->spreadsheet->getSheet($itemCriteria->SheetId);

                                        foreach ($arrRange as $key => $range) {
                                            //format của ô
                                            $formatCode = $worksheet->getCell($range)->getStyle()->getNumberFormat()->getFormatCode();
                                            $decimal = preg_match('/\.([?0]+)/u', $formatCode, $matches) ? strlen($matches[1]) : 0;  //decimal format

                                            if(preg_match('/(0+)(\\.?)(0*)/', $formatCode, $matches)){
                                                  //trường hợp decimal ko đúng yêu cầu nhưng dạng format đúng và ngược lại
                                                if((!str_contains($formatCode,'₫') && $decimal === $content->decimal) || (str_contains($formatCode,'đ') && $decimal !== $content->decimal)){
                                                    $criteria['RealScore'] = $criteria['Point'] / 2;
                                                    $criteria['Flag'] = true;
                                                    break;
                                                }
                                                else if(!str_contains($formatCode,'₫') || $decimal !== $content->decimal){
                                                    $criteria['Flag'] = false;
                                                    $criteria['RealScore'] = 0;
                                                }
                                            }
                                            else{
                                                $criteria['Flag'] = false;
                                                $criteria['RealScore'] = 0;
                                            }
                                        }
                                        break;
                                    case FormatNumber::Date:
                                        break;
                                    case FormatNumber::Time:
                                        break;
                                    case FormatNumber::Text:
                                        break;
                                    default:
                                        # code...
                                        break;
                                }

                                break;
                            case TypeProperty::FontSizeAll:
                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                $worksheet = $this->spreadsheet->getSheet($itemCriteria->SheetId);
                                $highestDataRow = $worksheet->getHighestDataRow();
                                $highestDataColumn = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());
                                $fontSize =  $this->spreadsheet->getSheet($itemCriteria->SheetId)->getCellByColumnAndRow($highestDataColumn-1,$highestDataRow+1)->getStyle()->getFont()->getSize();
                                if($fontSize != $itemCriteria->Content){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::FontAll:

                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                $worksheet = $this->spreadsheet->getSheet($itemCriteria->SheetId);
                                $highestDataRow = $worksheet->getHighestDataRow();
                                $highestDataColumn = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());

                                $fontName =  $this->spreadsheet->getSheet($itemCriteria->SheetId)->getCellByColumnAndRow($highestDataColumn-1,$highestDataRow+1)->getStyle()->getFont()->getName();
                                if(str_replace(' ','',$fontName) !== Font::getKey((int)$itemCriteria->Content)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                break;
                            case TypeProperty::Formula:
                                $content = json_decode($itemCriteria->Content,true);
                                $type_formula = (int)$content['typeFormula'];
                                $criteria['PropertyName'] = FormulaType::getKey((int)$type_formula);
                                $formulaExam = $content[1];
                                $listSheetIndex = [...array_keys($formulaExam[0]['listTitleSheet']),$itemCriteria->SheetId];
                                $calculation = Calculation::getInstance($this->spreadsheet);
                                //kiểm tra sự tồn tại của sheet
                                foreach ($listSheetIndex as $index) {
                                    if(!$this->sheetExists($index)){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                        break;
                                    }
                                }
                                //không tồn tại sheet
                                if(!$criteria['Flag']){
                                    break;
                                }
                                $result = [];
                                $isCopy = [];
                                //giá trị 2 công thức ko bằng nhau
                                $valueNotEqual = [];

                                $this->isDeviation = false;
                                foreach ($content['extractRange'] as $key => $range) {
                                    $valueNotEqual[$key] = false;
                                    $result[$key] = true;
                                    $titleReplace = [];
                                    //tên sheet cần thay thế
                                    foreach ($formulaExam[$key]['listTitleSheet'] as $keyTitle => $title) {
                                        $titleReplace[] = $this->listTitleSheet[$keyTitle];
                                    }
                                    //công thức trong đề
                                    $formulaExam[$key] = $this->updateTitleSheetFormula($formulaExam[$key],$type_formula,$titleReplace);
                                    //Lấy value tại ô cần kiểm tra công thức trong bài thi
                                    $cell = $this->spreadsheet->getSheet($itemCriteria->SheetId)->getCell($range);
                                    $formulaTest = $cell->getValue();
                                    //giá trị công thức bài thi
                                    $calculateFormulaTest = $cell->getCalculatedValue();
                                    //kiểm tra có phải công thức ko
                                    if($cell->getDataType() === DataType::TYPE_FORMULA){
                                        if($type_formula !== FormulaType::EXPRESSION){
                                            $needle = FormulaType::getKey((int)$type_formula);
                                            //kiểm tra xem có chứa đúng hàm hay ko
                                            if(stripos($cell->getValue(),$needle) === false){
                                                $result[$key] = false;
                                                goto UpdateFormula;
                                            }
                                        }
                                        try {
                                            $formulaTest = $this->convertArgumentFormula($formulaTest,$arrTable,$itemCriteria->SheetId,$type_formula,$range);
                                        } catch (\Throwable $th) {
                                            dd($formulaTest,$file,$this->list,$th);
                                        }
                                        switch ($type_formula) {
                                            case FormulaType::RANK:
                                            case FormulaType::MAX:
                                            case FormulaType::MIN:
                                            case FormulaType::AVERAGE:
                                                $formulaTest = preg_replace('/[$\'"\s]+/', '',$formulaTest[0]);
                                                if(preg_replace('/[$\'"\s]+/', '',$formulaExam[$key]['formula']) != $formulaTest){
                                                    $result[$key] = false;
                                                }
                                                break;
                                            case FormulaType::COUNTIF:
                                                $isCopy[] = $formulaTest[1];
                                                //xóa dấu ' và " trong công thức bài thi
                                                $formulaTest = preg_replace('/[$\'"\s]+/', '', $this->stripUnicode($formulaTest[0]));
                                                //trường hợp điều kiện là 1 địa chỉ=> có 2 công thức
                                                //1.chứa địa chỉ ô điều kiện E.g: =COUNTIF(sheet1!E3:E25,sheet1!A3)
                                                //2.chứa value ô điều kiện  E.g: =COUNTIF(sheet1!E3:E25,"Nam Định")
                                                if(is_array($formulaExam[$key]['formula'])){
                                                    $arrFormula = [];
                                                    //xóa dấu ' và " trong chuỗi gốc
                                                    foreach ($formulaExam[$key]['formula'] as $k => $value) {
                                                        $arrFormula[] = preg_replace('/[$\'"\s]+/', '', $this->stripUnicode($value));
                                                    }
                                                    //so sánh
                                                    if(!in_array($formulaTest,$arrFormula)){
                                                        $result[$key] = false;
                                                    }
                                                }
                                                else{
                                                    $formulaExam[$key]['formula'] = preg_replace('/[$\'"\s]+/', '', $this->stripUnicode($formulaExam[$key]['formula']));
                                                    if($formulaExam[$key]['formula'] != $formulaTest){
                                                        $result[$key] = false;
                                                    }
                                                }
                                                break;
                                            case FormulaType::IF:
                                                $arrMaster = $formulaExam[$key]['splitFormula'];
                                                $arrBranch = $formulaTest[0];
                                                foreach ($arrMaster as $keyM => $itemMaster) {
                                                    $flag = false;
                                                    if($itemMaster['valueIfTrue'] != null){
                                                        $find =  array_filter($arrBranch,function($itemBranch) use($itemMaster,$itemCriteria,$range){
                                                            $flag = false;
                                                            if($itemMaster['valueIfTrue'] && $itemBranch['valueIfTrue'] === $itemMaster['valueIfTrue'] || $itemBranch['valueIfFalse'] === $itemMaster['valueIfTrue']){
                                                                //nếu valueB trùng ở nhánh false sẽ phải so sánh biểu thức nhánh với đối của biểu thức gốc
                                                                $valueB = $itemBranch['valueIfTrue'] === $itemMaster['valueIfTrue'] ? true : false;

                                                                $valueB ? $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,true) :  $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,false);
                                                            }
                                                            return $flag;
                                                        });
                                                        if($find) $flag = true;
                                                    }
                                                    if(!$find){
                                                        $result[$key] = false;
                                                        break;
                                                    }
                                                    if($itemMaster['valueIfFalse'] != null){
                                                        $find = array_filter($arrBranch,function($itemBranch) use($itemMaster,$itemCriteria,$range){
                                                            $flag = false;
                                                            if($itemBranch['valueIfTrue'] === $itemMaster['valueIfFalse'] || $itemBranch['valueIfFalse'] === $itemMaster['valueIfFalse']){
                                                                //nếu valueB trùng ở nhánh false sẽ phải so sánh biểu thức nhánh với đối của biểu thức gốc
                                                                $valueB = $itemBranch['valueIfTrue'] === $itemMaster['valueIfFalse'] ? true : false;

                                                                $valueB ? $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,true) :  $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,false);
                                                            }
                                                            return $flag;
                                                        });
                                                        if($find) $flag = true;
                                                    }
                                                    if(!$find){
                                                        $result[$key] = false;
                                                        break;
                                                    }
                                                }
                                                break;
                                            case FormulaType::EXPRESSION:
                                                //so sánh các đối số của phép nhân
                                                if(!$this->array_equal($this->removeQuotesAndSpaces($formulaExam[$key]['splitFormula']['*']),$this->removeQuotesAndSpaces($formulaTest[1]['*']))){
                                                    $result[$key] = false;
                                                }
                                                else if(!$this->array_equal($this->removeQuotesAndSpaces($formulaExam[$key]['splitFormula']['+']),$this->removeQuotesAndSpaces($formulaTest[1]['+']))){
                                                    $result[$key] = false;
                                                }
                                                else if(!$this->array_equal($this->removeQuotesAndSpaces($formulaExam[$key]['splitFormula']['^']),$this->removeQuotesAndSpaces($formulaTest[1]['^']))){
                                                    $result[$key] = false;
                                                }
                                                $isCopy[] = $formulaTest[2];
                                                break;
                                            default:
                                                # code...
                                                break;
                                        }
                                    }
                                    else{
                                        $result[$key] = false;
                                        goto UpdateFormula;
                                    }

                                    //So sanhs value 2 công thức
                                    //trường hợp công thức đúng nhưng value sai
                                    if($result[$key] && $formulaExam[$key]['calculateFormula'] != $calculateFormulaTest){
                                        $criteria['Flag'] = true;
                                        $valueNotEqual[$key] = true;
                                        $criteria['RealScore'] = $criteria['Point'] / 2;
                                        break;
                                    }
                                    //công thức ko được copy
                                    if(count(array_unique($isCopy)) !== 1 && count($isCopy) > 0){
                                        $criteria['Flag'] = true;
                                        $criteria['RealScore'] = $criteria['Point'] / 2;
                                        break;
                                    }
                                    UpdateFormula:
                                    try {
                                        if(!$result[$key] && $this->gradingType && isset($this->listFormula[$itemCriteria->SheetId])){
                                            //tìm kiếm các công thức chứa trong sheet
                                            $findFormula = array_filter($this->listFormula[$itemCriteria->SheetId],function ($formula) use ($itemCriteria,$type_formula){
                                                $flag = true;
                                                if($type_formula !== FormulaType::EXPRESSION){
                                                    $needle = FormulaType::getKey((int)$type_formula);
                                                    //kiểm tra xem có chứa đúng hàm hay ko
                                                    if(stripos($formula['formula'],$needle) === false){
                                                        $flag = false;
                                                    }
                                                }
                                                return $flag;
                                            });

                                            $calculateFormulaTest = null;
                                            //duyệt các vùng tìm thấy
                                            foreach ($findFormula as $formula) {
                                                $formulaTest = $this->convertArgumentFormula($formula['formula'],$arrTable,$itemCriteria->SheetId,$type_formula,$formula['cellAddress']);
                                                $calculateFormulaTest = $formula['calculatedValue'];
                                                try {
                                                    //tiến hành so sánh lại với công thức gốc
                                                    if($this->compareFormula($formulaTest[0],$formulaExam[$key],$itemCriteria,$formula['cellAddress'],$type_formula,$isCopy)){
                                                        $this->isDeviation = true;
                                                        $result[$key] = true;
                                                        break;
                                                    }
                                                } catch (\Throwable $th) {
                                                        // dd($formulaTest,$cellValue,1,$this->list,$this->spreadsheet->getSheet(1)->getCell('B1'),$cellAddress,$file);
                                                }
                                            }
                                            //So sanhs value 2 công thức
                                            //trường hợp công thức đúng nhưng value sai
                                            if($result[$key] && $formulaExam[$key]['calculateFormula'] != $calculateFormulaTest){
                                                $criteria['Flag'] = true;
                                                $valueNotEqual[$key] = true;
                                                $criteria['RealScore'] = $criteria['Point'] / 2;
                                                break;
                                            }
                                        }
                                    } catch (\Throwable $th) {
                                        throw $th;
                                        // dd($result,$key,$formulaBranch,$formulaMaster,$file,$type_formula,$rangeFormulaBranch);
                                    }
                                }
                                try {
                                    $unique = array_unique($result);
                                    if(end($unique) == false){
                                        $criteria['Flag'] = false;
                                        $criteria['RealScore'] = 0;
                                    }
                                    else if(end($unique) == true){
                                        //cho phép chấm tương đối và có sự lệch vùng
                                        if($this->isDeviation && $this->gradingType){
                                                $criteria['Flag'] = true;

                                                if($this->deductedPoint > 0){
                                                    $criteria['RealScore'] = $criteria['Point'] * $this->deductedPoint;
                                                }
                                                else{
                                                    $criteria['RealScore'] = $criteria['Point'];
                                                }
                                            // // yêu cầu đặt ở vị trí tuyệt đối
                                            // if($itemCriteria->absolute_ccore){
                                            //     $criteria['Flag'] = true;
                                            //     $criteria['RealScore'] = $criteria['Point'] / 2;
                                            // }
                                            // //ko yêu cầu vị trí
                                            // else{
                                            //     $criteria['Flag'] = true;
                                            //     $criteria['RealScore'] = $criteria['Point'];
                                            // }
                                        }
                                        else{
                                            //công thức ko được copy hoặc có chứa ô có value ko bằng công thức gốc
                                            if(count(array_unique($isCopy)) !== 1 && count($isCopy)>0 || array_search(true,$valueNotEqual) !== false){
                                                $criteria['Flag'] = true;
                                                $criteria['RealScore'] = $criteria['Point'] / 2;
                                            }
                                            else{
                                                $criteria['Flag'] = true;
                                                $criteria['RealScore'] = $criteria['Point'];
                                            }
                                        }
                                    }
                                } catch (\Throwable $th) {
                                    dd((end($unique)),$result,$file,$th);
                                }
                                break;
                            case TypeProperty::SheetName:
                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                $title = $this->spreadsheet->getSheet($itemCriteria->SheetId)->getTitle();
                                switch ($itemCriteria->Content) {
                                    //số báo danh
                                    case SheetName::IdStudent:
                                        if(array_key_exists($studentIDExam,$students)){
                                            $students[$studentIDExam]['studentOrder'] = is_numeric($students[$studentIDExam]['studentOrder']) ? (int)$students[$studentIDExam]['studentOrder'] : $students[$studentIDExam]['studentOrder'];
                                            if($this->stripUnicode($students[$studentIDExam]['studentOrder']) != $this->stripUnicode($title)){
                                                $criteria['Flag'] = false;
                                                $criteria['RealScore'] = 0;
                                                break;
                                            }
                                        }
                                        else{
                                            $criteria['Flag'] = false;
                                            $criteria['RealScore'] = 0;
                                            break;
                                        }
                                        break;
                                    //mã sinh viên
                                    case SheetName::KeyStudent:
                                        if(array_key_exists($studentIDExam,$students)){
                                            if($studentIDExam !== $title){
                                                $criteria['Flag'] = false;
                                                $criteria['RealScore'] = 0;
                                                break;
                                            }
                                        }
                                        break;
                                    case SheetName::StudentName:
                                        if(isset($students[$studentIDExam])){
                                            //xóa dấu cách và dấu câu trong chuỗi nếu có
                                            if($this->stripUnicode(str_replace(' ','',mb_strtolower($students[$studentIDExam]['studentName']))) !== $this->stripUnicode(str_replace(' ','',mb_strtolower($title)))){
                                                $criteria['Flag'] = false;
                                                $criteria['RealScore'] = 0;
                                                break;
                                            }
                                        }
                                        else{
                                            $criteria['Flag'] = false;
                                            $criteria['RealScore'] = 0;
                                            break;
                                        }
                                        break;
                                    default:
                                        # code...
                                        break;
                                }
                                break;
                            case TypeProperty::AdvancedFilter:
                                $criteria['PropertyName'] = 'Advanced Filter';
                                $content = json_decode($itemCriteria->Content)[0];
                                //sheet ko tồn tại ở vị trí đặt vùng trả về dữ liệu
                                if(!$this->sheetExists($itemCriteria->SheetId)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }

                                 //sheet ko tồn tại ở vị trí đặt vùng trả về dữ liệu
                                 if(!$this->sheetExists($content->SheetIndex)){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                    break;
                                }
                                $title = $this->spreadsheet->getSheet((int)$content->SheetIndex)->getTitle();
                                //vùng tiêu chí advanced
                                $criteriaRange = "{$title}!{$content->TitleRange}:{$content->CriteriaRange}";
                                //tìm vùng tiêu chí trong file bài thi
                                $isCriteriaRange = array_filter($arrName,function($defineName) use ($criteriaRange){
                                    return str_replace("'",'',$defineName['range']) === $criteriaRange && $defineName['subName'] == '_xlnm.Criteria';
                                });
                                //nếu ko có
                                if(!$isCriteriaRange){
                                    $criteria['Flag'] = false;
                                    $criteria['RealScore'] = 0;
                                }
                                else{
                                    $data_reference = json_decode($itemCriteria->DataReference);
                                    $rangeDataMaster[0] = $data_reference->range;

                                    try {
                                        $rangeDataMaster[1] = Coordinate::splitRange($rangeDataMaster[0])[0][0];  //ô dữ liệu đầu tiên E.g:A4
                                        $rangeDataMaster[0] = Coordinate::rangeDimension($rangeDataMaster[0])[0]; //chiều dài vùng dữ liệu
                                    } catch (\Throwable $th) {
                                        throw $th;
                                        dd(3);
                                    }
                                    $rangeDataMaster[2] = $data_reference->sheetIndex;
                                    foreach ($arrName as $key => $defineName) {
                                        //tìm vùng trả dữ liệu filter
                                        if(str_contains($defineName['range'],$title) && $defineName['subName'] == '_xlnm.Extract'){
                                            $rangeDataTest = explode('!',$defineName['range']);
                                            $rangeDataTest[0] = end($rangeDataTest);
                                            $rangeDataTest[1] =  Coordinate::splitRange($rangeDataTest[0])[0][0];
                                            $rangeDataTest[0] = Coordinate::rangeDimension($rangeDataTest[0])[0];
                                            // if($rangeDataMaster[0] !== $rangeDataTest[0]){
                                            //     $criteria['Flag'] = false;
                                            //     $criteria['RealScore'] = 0;
                                            //     break;
                                            // }

                                            // dd($defineName['range']);
                                            //kiểm tra value của ô đầu tiên của vùng trả dữ liệu
                                            $valueCellMaster = $this->spreadsheet->getSheet($data_reference->sheetIndex)->getCell($rangeDataMaster[1])->getValue();
                                            $valueCellTest = $this->spreadsheet->getSheet($itemCriteria->SheetId)->getCell($rangeDataTest[1])->getValue();
                                            if($valueCellMaster !== $valueCellTest){
                                                $criteria['Flag'] = false;
                                                $criteria['RealScore'] = 0;
                                                break;
                                            }

                                            break;
                                        }
                                    }

                                    // dd($criteria);
                                }
                                break;
                            }
                        $totalScore += $criteria['RealScore'];
                        array_push($criteriaList,$criteria);
                    }

                    $itemExam['Path'] = $path.'/'.$file;
                    $itemExam['TotalScore'] =  $totalScore;
                    $itemExam['CandidateNumber'] =  $students[substr($file,0,9)]['studentOrder'] ?? '';
                    $itemExam['StudentCode'] = substr($file,0,9);
                    $itemExam['StudentName'] = $students[substr($file,0,9)]['studentName'] ?? '';
                    array_push($listExam,$itemExam);
                    $listExamDetail[substr($file,0,9)] = $criteriaList;  //mã sinh viên
                    $this->list[substr($file,0,9)] = substr($file,0,9);
                }
            }


           try {
                DB::begintransaction();
                examresult::insert($listExam); // remember: $data is array of associative array. Not just a single assoc array.
                // dd($this->list);
                foreach ($listExamDetail as $key => $value) {
                    examresultdetail::insert($value);
                }
                DB::commit();
           } catch (\Throwable $th) {
                dd($th);
           }
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        dd(3);
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
        dd(3);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Lưu thông tin tiêu chí
     */
    public function saveCriteria(Request $request){
        $listCriteria = $request[0];
        $fileData = $request[1];

        $this->spreadsheet = IOFactory::load("{$fileData['ResourcePath']}/{$fileData['ResourceFile']}");
        //tiến hành thiết lập vào file tiêu chí để lấy thông tin chính xác
        foreach ($listCriteria as $key => $criteria) {
            switch ((int)$criteria['TypeProperty']) {
                case TypeProperty::Text:
                    $this->spreadsheet->getSheet($criteria['SheetId'])->getCell($criteria['Range'])->setValue($criteria['Content']);
                    break;
                case TypeProperty::Formula:
                    $content = json_decode($criteria['Content']);
                    $type_formula = (int)$content[0];
                    $formula = $content[1];
                    $listCriteria[$key]['Content'] = [];
                    $listCriteria[$key]['Content']['typeFormula'] = $content[0];
                    $extractRange = Coordinate::extractAllCellReferencesInRange($criteria['Range']);
                    $listCriteria[$key]['Content']['extractRange'] = $extractRange;
                    $worksheet = $this->spreadsheet->getSheet($criteria['SheetId']);
                    $formula = $this->convertFormulaExam($formula,$type_formula);
                    foreach ($extractRange as $range) {
                        $listCriteria[$key]['Content'][1][] = $formula;
                        $worksheet->getCell($range)->setValue($formula['formula']);
                        $formula = $this->updateFormulaExam($formula,$type_formula);
                    }
                    break;
                default:
                    break;
            }
        }

        //lấy ra các giá trị công thức
        foreach ($listCriteria as $key => $criteria) {
            switch ((int)$criteria['TypeProperty']) {
                case TypeProperty::Formula:
                    $worksheet = $this->spreadsheet->getSheet($criteria['SheetId']);
                    foreach ($criteria['Content']['extractRange'] as $keyRange => $range) {
                        $listCriteria[$key]['Content'][1][$keyRange]['calculateFormula'] = $worksheet->getCell($range)->getCalculatedValue();
                        switch ((int)$listCriteria[$key]['Content']['typeFormula']) {
                            case FormulaType::COUNTIF:
                                $formula = $listCriteria[$key]['Content'][1][$keyRange]['formula'];
                                $search = $listCriteria[$key]['Content'][1][$keyRange]['splitFormula'][1];
                                $extractSheetTitle =  Worksheet\Worksheet::extractSheetTitle($search,true);
                                //điều kiện là địa chỉ E.g: =COUNTIF('Sheet1'!B3:B25,'Sheet1'!B6)
                                if($this->cellExits($extractSheetTitle[1])){
                                    $criteriaValue = $this->spreadsheet->getSheetByName($extractSheetTitle[0])->getCell($extractSheetTitle[1])->getValue();
                                    $listCriteria[$key]['Content'][1][$keyRange]['formula'] = [];
                                    $listCriteria[$key]['Content'][1][$keyRange]['formula'][] = $formula;
                                    $listCriteria[$key]['Content'][1][$keyRange]['formula'][] = str_replace($search,"\"{$criteriaValue}\"",$formula);
                                }
                                break;

                            default:
                                # code...
                                break;
                        }
                    }
                    $listCriteria[$key]['Content'] = json_encode($listCriteria[$key]['Content']);
                    break;
                default:
                    break;
            }
        }

        try {
            DB::beginTransaction();
            criteria::insert($listCriteria);
            DB::commit();
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /**
     * Xử lý công thức
     */
    function convertFormulaExam($formula,$type_formula){
        $calculation = Calculation::getInstance($this->spreadsheet);
        $calculation->flushInstance();
        $parseFormula = $calculation->parseFormula($formula);
        //Mảng lưu tên các sheet
        $listTitleSheet = [];
        $arguments = [];
        foreach($parseFormula as $key => $tokenData){
            //convert địa chỉ E.g: E1 => sheet1!E1
            if($parseFormula[$key]['type'] === FormulaType::CellReference){
                $explodeCellAddress = explode('!',$tokenData['value']);
                //tên sheet
                $title = $this->spreadsheet->getSheet($explodeCellAddress[0])->getTitle();
                $arguments[] = $explodeCellAddress[1];
                //Mảng lưu tên các sheet để tiến hành cập nhập sau này
                $listTitleSheet[$explodeCellAddress[0]] = $title;
                //địa chỉ cell E.g: A2 => 'sheet1'!A2
                $cellAddress = "'{$title}'!{$explodeCellAddress[1]}";
                //thay thế vào công thức
                $formula = str_replace($tokenData['value'],$cellAddress,$formula);
                $parseFormula[$key]['value'] = $cellAddress;
            }
        }
        $splitFormula = null;
        switch ($type_formula) {
            case FormulaType::IF:
                //tách các đối số trong hàm
                $splitFormula =  $this->splitIfStatement($formula,$result = [], $index = 0);
                break;
            case FormulaType::EXPRESSION:
                $splitFormula = $this->parseExpression($formula);
                break;
            case FormulaType::COUNTIF:
                // //lấy các đối số trong ngoặc
                if(preg_match('/\((.*)\)/', $formula, $matches)){
                    $splitFormula = explode(',',$matches[1]);
                }
                break;
            case FormulaType::RANK:
                //=RANK(1!E3,1!E3:E16,0) lấy ra các đối số E.g: 1!E3,1!E3:E16,0
                if(preg_match('/\((.*)\)/', $formula, $matches)){
                    $parts = explode(',',$matches[1]); //tách nhỏ
                    $splitFormula = $parts[0];
                }
                break;
            case FormulaType::MAX:
            case FormulaType::MIN:
            case FormulaType::AVERAGE:
                preg_match('/\((.*)\)/', $formula, $matches);
                $arguments = explode(',',$matches[1]);
                foreach ($arguments as $key => $argument) {
                    $range =  Coordinate::extractAllCellReferencesInRange($argument);
                    $formula = str_replace($argument,join(',',$range),$formula);
                }
                break;
            default:
                # code...
                break;
        }
        return ['formula' => $formula,'listTitleSheet' => $listTitleSheet,'oldArgument' => $arguments, 'argument' => $arguments,'splitFormula' => $splitFormula];
    }

    /**
     * Cập nhật công thức trong đề thi
     *
     */
    public function updateFormulaExam($formula,$type_formula){
        switch ($type_formula) {
            case FormulaType::IF:
            case FormulaType::EXPRESSION:
                foreach ($formula['argument'] as $key => $argument) {
                    if(!str_contains($argument,'$')){
                        $search = $formula['argument'][$key];
                        $coordinateFromString =  Coordinate::coordinateFromString($argument);
                        //cập nhật đối số E.g: E3 => E4
                        $cellAddress = (new CellAddress($argument))->nextRow()->cellAddress();
                        $formula['argument'][$key] = $cellAddress;
                        //cập nhật công thức
                        $formula['formula'] = str_replace($search,$cellAddress,$formula['formula']);
                    }
                }
                //cập nhật các đối số tách nhỏ của hàm
                foreach ($formula['splitFormula'] as $key1 => $item) {
                    foreach ($item as $key2 => $child) {
                        $formula['splitFormula'][$key1][$key2] = str_replace($formula['oldArgument'],$formula['argument'],$formula['splitFormula'][$key1][$key2]);
                    }
                }
                $formula['oldArgument'] = $formula['argument'];
                break;
            case FormulaType::COUNTIF:
                if(str_contains($formula['splitFormula'][1],'!') && !str_contains($formula['splitFormula'][1],'$')){
                    $search = $formula['splitFormula'][1];
                    //tách sheet và địa chỉ E.g: 'sheet1'!A3 => [sheet1,A3]
                    $extractSheetTitle =  Worksheet\Worksheet::extractSheetTitle($formula['splitFormula'][1],true);
                    //cập nhật đối số E.g: E3 => E4
                    $cellAddress = (new CellAddress($extractSheetTitle[1]))->nextRow()->cellAddress();
                    $extractSheetTitle[1] = str_replace($extractSheetTitle[1],$cellAddress,$extractSheetTitle[1]);

                    $cellAddress = "{$extractSheetTitle[0]}!{$cellAddress}";
                    //cập nhật các đối số
                    $formula['splitFormula'][1] = $cellAddress;
                    //cập nhật công thức
                    $formula['formula'] = str_replace($search,$cellAddress,$formula['formula']);
                }
                break;
            case FormulaType::RANK:
                $search = $formula['splitFormula'];
                $extractSheetTitle =  Worksheet\Worksheet::extractSheetTitle($formula['splitFormula'],true);
                 //cập nhật đối số E.g: E3 => E4
                $extractSheetTitle[1] = (new CellAddress($extractSheetTitle[1]))->nextRow()->cellAddress();
                $extractSheetTitle = "{$extractSheetTitle[0]}!{$extractSheetTitle[1]}";

                $formula['formula'] = str_replace($search,$extractSheetTitle,$formula['formula']);
                $formula['splitFormula'] = $extractSheetTitle;
                break;
            default:
                # code...
                break;
        }
        return $formula;
    }

    /**
     * convert công thức if
     * @param string $formula công thức
     * @param array $arrTable Các table đọc được từ bài thi
     * @param string $cellAddress địa chỉ ô cần tính
     * @param bool $type true: công thức gốc, false công thức bài thi
     * @return array [công thức,giá trị true&false,biểu thức logic,true|false cho công thức gốc,giá trị công thức]
     * **/
    function parseIf($formula){
        $result = [];
        $index = 0;
        $splitIfStatement =  $this->splitIfStatement($formula,$result,$index);
        $values = [];
        //xử lý giá trị trùng
        foreach ($splitIfStatement as $key => $valueIF) {
            //tìm kiếm giá trị false trùng nhau
            $findDuplicateFalse =  array_filter($splitIfStatement,function($obj) use($valueIF){
                return $obj['valueIfFalse'] == $valueIF['valueIfFalse'] && !is_null($obj['valueIfFalse']);
            });
            //nếu có
            if(count($findDuplicateFalse)>1){
                unset($findDuplicateFalse[0]);
                foreach ($findDuplicateFalse as $keyD => $valueD) {
                    if($splitIfStatement[$key]['Logic'][0] === 'OR'){
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);
                        $splitIfStatement[$key]['valueIfTrue'] = $valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue'];
                        array_splice($splitIfStatement,$keyD,1);
                    }
                    else{
                        array_splice($splitIfStatement[$key]['Logic'],0, 0, 'OR' );
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);

                        $splitIfStatement[$key]['valueIfTrue'] = $valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue'];
                        array_splice($splitIfStatement,$keyD,1);
                    }
                }
            }

            $findDuplicateTrue =  array_filter($splitIfStatement,function($obj) use($valueIF){
                return $obj['valueIfTrue'] == $valueIF['valueIfTrue'];
            });

            //kiểm tra các giá trị value if true trùng
            //nếu trùng thì thực hiện nối các biểu thức thành biểu thức and
            //e.g: AND(E3>5,E5>6)
            if(count($findDuplicateTrue)>1){
                unset($findDuplicateTrue[0]);
                foreach ($findDuplicateTrue as $keyD => $valueD) {
                    if($splitIfStatement[$key]['Logic'][0] === 'AND'){
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);
                        $splitIfStatement[$key]['valueIfTrue'] = preg_replace('/[\"\s]+/', '', $this->stripUnicode($valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue']));
                        array_splice($splitIfStatement,$keyD,1);
                    }
                    else{
                        array_splice($splitIfStatement[$key]['Logic'],0, 0, 'AND' );
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);

                        $splitIfStatement[$key]['valueIfTrue'] = $valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue'];
                        array_splice($splitIfStatement,$keyD,1);
                    }
                }
            }

            // $values[] = ['valueIfTrue'=>$valueIF['valueIfTrue'],'valueIfFalse'=>$valueIF['valueIfFalse']];
        }
        return [$splitIfStatement];
    }

    /**
     * Convert địa chỉ trong công thức
     * Thực hiện chuyển đổi các địa chỉ dạng StructuredReference, namedCell sang cellAddress E.g: Table1[[#This Row],[Giá phòng] => 'SBD01-MSV705906042'!D3
     * E.g:=(Table1[[#This Row],[Số ngày]]*Table1[[#This Row],[Giá phòng]]) => =('SBD01-MSV705906042'!E3*'SBD01-MSV705906042'!D3)
     * @param array $sheetDatas dữ liệu của các sheet đọc được từ bài thi
     * @param array $listTable Các table trong bài thi
     * **/
    public function handlerFormulaAddress($sheetDatas,$listTable){
        //duyệt dữ liệu từng sheet
        foreach ($sheetDatas as $sheetIndex => $listRow) {
            $worksheet = $this->spreadsheet->getSheet($sheetIndex);
            $title = $worksheet->getTitle();
            //duyệt dữ liệu từng hàng trong sheet
            foreach ($listRow as $rowIndex => $listColumn) {
                foreach ($listColumn as $columnName => $cellData) {
                    $cellAddress = "{$columnName}{$rowIndex}";
                    $cell = $worksheet->getCell($cellAddress);
                    //convert các ô là công thức
                    if($cell->getDataType() === DataType::TYPE_FORMULA){
                        $formula = $cell->getValue();
                        $calculation = Calculation::getInstance($this->spreadsheet);
                        $calculation->flushInstance();
                        //convert
                        $parseFormula = $calculation->parseFormula($formula);
                        foreach ($parseFormula as $tokenData) {
                            //convert địa chỉ nằm trong table
                            if($tokenData['type'] === FormulaType::StructuredReference){
                                $address = $this->parseStructuredReference($tokenData['value']->value(),$listTable,$cellAddress); //địa chỉ đã được convert
                                //trường hợp chứa header
                                if(str_contains($tokenData['value']->value(),'#All') && $this->validateRange($address)){
                                    $exploded = explode(':',$address);
                                    $extractSheetTitle =  Worksheet\Worksheet::extractSheetTitle($exploded[0],true);
                                    //cập nhật đối số E.g: E3 => E4
                                    $extractSheetTitle[1] = (new CellAddress($extractSheetTitle[1]))->nextRow()->cellAddress();
                                    $address = "{$extractSheetTitle[0]}!{$extractSheetTitle[1]}:{$exploded[1]}";
                                }
                                $formula = str_replace($tokenData['value']->value(),$address,$formula);
                            }
                            //convert địa chỉ ko chứa tên sheet E.g: E3 => 'sheet1'!E3
                            else  if($tokenData['type'] === FormulaType::CellReference && !str_contains($tokenData['value'],'!')){
                                //thay thế địa chỉ: E.g: E3 => sheet1!E3 (tránh lặp sheet1sheet1!E3)
                               $formula = preg_replace('/(?<=[^!])\b' . preg_quote($tokenData['value'], '/') . '\b/i', "'{$title}'!{$tokenData['value']}", $formula);
                            }
                        }
                        $worksheet->setCellValueExplicit($cellAddress,$formula,DataType::TYPE_FORMULA);
                        $calculatedValue = $worksheet->getCell($cellAddress)->getCalculatedValue();
                        $this->listFormula[$sheetIndex][] = ['formula' => $formula, 'cellAddress' => $cellAddress, 'calculatedValue' => $calculatedValue];
                    }
                }
            }
        }
    }

    public function compareFormula($formulaTest,$formulaExam,$itemCriteria,$range,$type_formula,$isCopy){
        $flag = true;
        switch ($type_formula) {
            case FormulaType::RANK:
            case FormulaType::MAX:
            case FormulaType::MIN:
            case FormulaType::AVERAGE:
                $formulaTest = preg_replace('/[$\'"\s]+/', '',$formulaTest);
                if(preg_replace('/[$\'"\s]+/', '',$formulaExam['formula']) != $formulaTest){
                    $flag = false;
                }
                break;
            case FormulaType::COUNTIF:
                //xóa dấu ' và " trong công thức bài thi
                // $formulaTest = preg_replace('/[\'"]/', '', $this->stripUnicode($formulaTest));
                 //xóa dấu ' và " trong công thức bài thi
                 $formulaTest = preg_replace('/[$\'"\s]+/', '', $this->stripUnicode($formulaTest));
                //trường hợp điều kiện là 1 địa chỉ=> có 2 công thức
                //1.chứa địa chỉ ô điều kiện E.g: =COUNTIF(sheet1!E3:E25,sheet1!A3)
                //2.chứa value ô điều kiện  E.g: =COUNTIF(sheet1!E3:E25,"Nam Định")
                if(is_array($formulaExam['formula'])){
                    $arrFormula = [];
                    //xóa dấu ' và " trong chuỗi gốc
                    foreach ($formulaExam['formula'] as $k => $value) {
                        $arrFormula[] = preg_replace('/[$\'"\s]+/', '', $this->stripUnicode($value));
                    }

                    //so sánh
                    if(!in_array($formulaTest,$arrFormula)){
                        $flag = false;
                    }
                }
                else{
                    $formulaExam['formula'] = preg_replace('/[$\'"\s]+/', '', $this->stripUnicode($formulaExam['formula']));
                    if($formulaExam['formula'] != $formulaTest){
                        $flag = false;
                    }
                }

                break;
            case FormulaType::IF:
                $arrMaster = $formulaExam['splitFormula'];
                $arrBranch = $formulaTest[3];
                foreach ($arrMaster as $keyM => $itemMaster) {
                    $flag = false;
                    foreach ($arrBranch as $keyB => $itemBranch) {
                        if($itemMaster['valueIfTrue'] && $itemBranch['valueIfTrue'] === $itemMaster['valueIfTrue'] || $itemBranch['valueIfFalse'] === $itemMaster['valueIfTrue']){
                            //nếu valueB trùng ở nhánh false sẽ phải so sánh biểu thức nhánh với đối của biểu thức gốc
                            $valueB = $itemBranch['valueIfTrue'] === $itemMaster['valueIfTrue'] ? true : false;

                            $valueB ? $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,true) :  $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,false);

                        }
                        else if($itemMaster['valueIfFalse'] && $itemBranch['valueIfTrue'] === $itemMaster['valueIfFalse'] || $itemBranch['valueIfFalse'] ===$itemMaster['valueIfFalse']){
                            //nếu valueB trùng ở nhánh false sẽ phải so sánh biểu thức nhánh với đối của biểu thức gốc
                            $valueB = $itemBranch['valueIfTrue'] === $itemMaster['valueIfFalse'] ? true : false;

                            $valueB ? $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,true) :  $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],$itemCriteria->SheetId,$range,false);
                        }

                         //nếu tìm thấy và biểu thức bằng nhau thì dừng
                        if($flag){
                            break;
                        }
                    }
                    $flag = $flag;
                }

                break;
            case FormulaType::EXPRESSION:
                if(!$this->array_equal($this->removeQuotesAndSpaces($formulaExam['splitFormula']['*']),$this->removeQuotesAndSpaces($formulaTest[1]['*']))){
                    $flag = false;
                }
                else if(!$this->array_equal($this->removeQuotesAndSpaces($formulaExam['splitFormula']['+']),$this->removeQuotesAndSpaces($formulaTest[1]['+']))){
                    $flag = false;
                }
                else if(!$this->array_equal($this->removeQuotesAndSpaces($formulaExam['splitFormula']['^']),$this->removeQuotesAndSpaces($formulaTest[1]['^']))){
                    $flag = false;
                }
                $isCopy[] = $formulaTest[2];
                break;
            default:
                # code...
                break;
        }

        return $flag;
    }

    public function updateTitleSheetFormula($formula,$type_formula,$titleSheet){
        $formula['formula'] = str_replace($formula['listTitleSheet'],$titleSheet,$formula['formula']);
        switch ($type_formula) {
            case FormulaType::IF:
                foreach ($formula['splitFormula'] as $key1 => $item) {
                    $stringArgumentLogic = join('::',$formula['splitFormula'][$key1]['Logic']);
                    $stringArgumentLogic = str_replace($formula['listTitleSheet'],$titleSheet,$stringArgumentLogic);
                    $formula['splitFormula'][$key1]['Logic'] = explode('::',$stringArgumentLogic);
                }
                break;
            case FormulaType::EXPRESSION:
                foreach ($formula['splitFormula'] as $key1 => $item) {
                    if(!empty($item)){
                        if(!empty($this->deviation) && $this->gradingType){
                            //tách địa chỉ E.g: A1 => [A,1]
                            foreach ($item as $key2 => $child) {
                                if(!str_contains($child,'$')){
                                    //tách tên sheet và địa chỉ E.g: 'Sheet1'!E3 => ['sheet1',E3]
                                    $extractSheetTitle =  Worksheet\Worksheet::extractSheetTitle($child,true);
                                    //tách địa chỉ cell E.g: E3 => [E,3]
                                    $coordinateFromString = Coordinate::coordinateFromString($extractSheetTitle[1]);
                                    //tồn tại địa chỉ trong mảng vùng lệch
                                    if(isset($this->deviation[$coordinateFromString[0]])){
                                        //cập nhật lại địa chỉ
                                        //E.g: A1 => G2
                                        $columnName = $coordinateFromString[0];
                                        $coordinateFromString[0] = $this->deviation[$columnName]['toColumn'];
                                        $coordinateFromString[1] += $this->deviation[$columnName]['toRow'];
                                        $extractSheetTitle[1] = join('',$coordinateFromString);
                                        $formula['splitFormula'][$key1][$key2] = join('!',$extractSheetTitle);
                                        $this->isDeviation = true;
                                    }
                                }
                            }
                                // if(str_contains($parts,array_keys($this->deviation)));
                        }
                        $stringArgument = join('::',$formula['splitFormula'][$key1]);
                        $stringArgument = str_replace($formula['listTitleSheet'],$titleSheet,$stringArgument);
                        $formula['splitFormula'][$key1] = explode('::',$stringArgument);

                    }
                }
                break;
            default:
                # code...
                break;
        }
        return $formula;
    }

    /**
     * Xóa dấu ' và khoảng trắng của các
     * phần tử trong mảng
     * E.g: ['sheet1'!E3, 'sheet1'! E4]  =>  [sheet1!E3, sheet1!E4]
     * @param array $arr Mảng cần xóa
     * **/
    public function removeQuotesAndSpaces($arr){
        if(!empty($arr)){
            $arr = explode('::',preg_replace('/[\'\s]+/', '',join('::',$arr)));
        }
        return $arr;
    }

    /**
     * Chuyển đổi vùng thành địa chỉ ô
     * E.g: sheet1!B2:B5 => sheet1!B4 || sheet1!B2:sheet1!B5 => sheet1!B4
     * @param string $range E.g: B2:B5 | sheet1!B2:B5 || sheet1!B2:sheet1!B5
     * @param int $sheetIndex Vị trí sheet chứa vùng
     * @param string $cellAddress
     * @return array  [true => convert thành công | false => thất bại , range=> địa chỉ convert]
     * **/
    function parseRangetoCellAddress($range,$sheetIndex,$cellAddress){
        $splitRange = Coordinate::splitRange($range)[0];
        //tên sheet chứa vùng cần chuyển đổi
        $title = $this->spreadsheet->getSheet($sheetIndex)->getTitle();

        $listTitle = [];
        //lưu địa chỉ các column E.g: B2:C5 => [B,C] || B2:B5 => [B,B]
        $listColumn = [];

        $listAddress = [];
        foreach ($splitRange as $itemAddress) {
            //E.g: sheet1!E3 => [sheet1, E3]
            $explode = explode('!',$itemAddress);
            //E.g: [sheet1, E3] || [E3]
            if(count($explode)>1){
                $listTitle[] = str_replace("'",'',$explode[0]);
                $indexesFromString = Coordinate::indexesFromString($explode[1]);
                $listColumn[] = $indexesFromString[2];
                $listAddress[] = $indexesFromString[1];
            }
            else{
                $listTitle[] = $title;
                if($this->cellExits($itemAddress)){
                    $indexesFromString = Coordinate::indexesFromString($explode[0]);
                    $listAddress[] = $indexesFromString[1];
                    $listColumn[] = $indexesFromString[2];
                }
            }
        }
        //E.g: sheet1!E3:sheet1!E6 || sheet1!E3:E6 => sheet1!E5
        if(count(array_unique($listTitle)) === 1 && count(array_unique($listColumn)) === 1){

            //lấy ra địa chỉ hàng E.g: C5 => [C,5]
            $coordinateFromString = Coordinate::coordinateFromString($cellAddress);
            //hàng phải nằm trong vùng hàng

            try {
                if($coordinateFromString[1] >= $listAddress[0] && $coordinateFromString[1] <= $listAddress[1]){
                    $range = "'{$listTitle[0]}'!{$listColumn[0]}{$coordinateFromString[1]}";
                    return [true,$range];
                }
            } catch (\Throwable $th) {
                throw $th;
               dd($coordinateFromString,$listAddress,$range,4,$cellAddress,$range,$listAddress);
            }
        }
        return [false,$range];
    }

    /**
     * Kiểm tra xem có phải địa chỉ cell ko
     * E.g: A =>false, A1 =>true
     * @param string $cellAddress Chuỗi cần kiểm tra
     * @return bool true|false
     * **/
    function cellExits($cellAddress){
        if(preg_match('/^' . Calculation::CALCULATION_REGEXP_CELLREF . '$/miu', $cellAddress) !== 1){
            return false;
        }
        return true;
    }


    /**
     * Convert địa chỉ các tham số của công thức
     * về dạng tên sheet!address  (E.g: sheet1!A1)
     * @param string $formula công thức cần convert
     * @param array $arrTable Mảng table đọc được từ bài thi
     * @param int   $sheetIndex sheet lấy dữ liệu dùng cho công thức
     * @param int   $type Loại công thức (SUM,COUNT...)
     * @param string    $cellAddress vị trí cell đặt công thức
     * @return string|array $formula đã được convert về dạng tương ứng
     * **/
    function convertArgumentFormula($formula,$arrTable,$sheetIndex,$type,$cellAddress){
        $calculation = Calculation::getInstance($this->spreadsheet);
        $title = $this->spreadsheet->getSheet($sheetIndex)->getTitle();  //tiêu đề sheet
        switch ($type) {
            case FormulaType::COUNTIF:
            case FormulaType::SUMIF:
                $flag = 1;
               //lấy các đối số trong ngoặc
               if(preg_match('/\((.*)\)/', $formula, $matches)){
                   $search = $matches[1];
                   $replace = $matches[1];             //lấy ra thông tin công thức trong cặp dấu () E.g: =COUNTIF(Sheet1!E3:E17,">=5") => Sheet1!E3:E17,">=5"
                   $replace = explode(',',$replace);   //tách các đối số
                   foreach ($replace as $key => $item) {
                       if($key === 1){             //điều kiện
                           $flag = 1; //cờ điều kiện : 1: string | 2: address | 3: range
                           $replace[$key] = preg_replace('/(?<![<>])=/', '', $replace[$key]);  //xóa dấu = nếu trước dấu = ko phải dấu < or >
                           //E.g: =COUNTIF(20!B3:B25,20!B2:B6)
                           if($this->validateRange($replace[$key])){
                                $parseRangetoCellAddress = $this->parseRangetoCellAddress($replace[$key],$sheetIndex,$cellAddress);
                               if($parseRangetoCellAddress[0]){
                                   $replace[$key] = "\"{$this->spreadsheet->getSheet($sheetIndex)->getCell($parseRangetoCellAddress[1])->getValue()}\"";
                               }
                           }else if($this->cellExits($replace[$key])){
                                $flag = 2;
                                $replace[$key] = "\"{$this->spreadsheet->getSheet($sheetIndex)->getCell($replace[$key])->getValue()}\"";
                           }
                       }
                       else if($key == 0){
                            //E.g: '19'!E3:'19'!E25 => '19'!E3:E25
                            if($this->validateRange($replace[$key])){
                                $replace[$key] = $this->handlerRangeReferences3D($replace[$key]);
                            }
                       }
                   }
                   $replace = join(",",$replace);
                   $formula = str_replace($search,$replace,$formula);  //công thức gốc sau convert
                }
                // dd($formula);
                return [$formula,$flag];
            case FormulaType::MAX:
            case FormulaType::MIN:
            case FormulaType::AVERAGE:
                //lấy ra các đối số trong hàm
                $arguments = $this->getArgumentsFormula($formula);
                foreach ($arguments as $key => $argument) {
                    if($this->validateRange($argument)){
                        $range = Coordinate::extractAllCellReferencesInRange($this->handlerRangeReferences3D($argument));
                        $replace = join(',',$range);
                        $formula = str_replace($argument,$replace,$formula);
                    }
                }
                return [$formula];
                break;
            case FormulaType::SUM:
                //lấy ra các công thức sum (nếu có nhiều e.g: =SUM(E5:G5)-SUM(E6:G6) => [SUM(E5:G5) , SUM(E6:G6)])
                if(preg_match_all('/SUM\([^)]+\)/', $formula, $matches)){
                    $listSum = $matches[0];
                    foreach ($listSum as $item) {
                        //lấy ra các đối số trong công thức E.g: =SUM(E5:G5)   => E5:G5
                        if(preg_match('/\((.*)\)/', $item, $matches)){
                            $exploded = explode(',',$matches[1]);
                            foreach ($exploded as $argument) {

                                if($this->validateRange($argument)){
                                    $range = Coordinate::extractAllCellReferencesInRange($this->handlerRangeReferences3D($argument));
                                    $formula = str_replace($argument,join(',',$range),$formula);
                                }
                            }
                        }
                    }
                }
                break;
            case FormulaType::RANK:
                if(preg_match('/\((.*)\)/', $formula, $matches)){
                    $search = $matches[1];
                    //lấy ra các đối số trong hàm
                    $arguments = $this->getArgumentsFormula($formula);
                    $replace = $arguments;
                    //xử lý trường hợp điều kiện là 1 vùng E.g: =RANK('01'!E3:E25,'01'!E3:E25,1) => =RANK('01'!E3,'01'!E3:E25,1)
                    if($this->validateRange($arguments[0])){
                        $parseRangetoCellAddress = $this->parseRangetoCellAddress($arguments[0],$sheetIndex,$cellAddress);
                        if($parseRangetoCellAddress[0]){
                            $replace[0] = $parseRangetoCellAddress[1];
                        }
                    }
                    $arguments = join(',',$arguments);
                    $replace = join(',',$replace);
                    $formula = str_replace($arguments,$replace,$formula);
                }
                return [$formula];
            case FormulaType::IF:
                $formula = $this->parseIf($formula);
                return $formula;
                break;
            case FormulaType::EXPRESSION:
                $flag = false;   //cờ: true: nếu sử dụng công thức, false: nếu ko
                $calculation = Calculation::getInstance($this->spreadsheet);
                //trường hợp sử dụng hàm
                while (str_contains($formula,'PRODUCT')) {
                    $flag = true;
                    if(preg_match('/PRODUCT\([^\)]+\)/', $formula, $matches)){
                        //lấy ra chuỗi trong ngoặc E.g: PRODUCT(E3,E5) => E3,E5
                        preg_match('/\((.*)\)/', $matches[0], $preg);
                        // Coordinate::extractAllCellReferencesInRange($arguments);
                        $arguments = explode(',',$preg[1]);
                        foreach ($arguments as $key => $arg) {
                            //phải thực hiện xóa tên sheet nếu đang là vùng
                            //E.g: 11!E3:11!D5 => 11!E3:D5 (ko xóa sử dụng hàm sẽ lỗi)
                            if(str_contains($arg,':')){
                                $arg = explode(':',$arg);
                                $arg[1] = substr($arg[1],strpos($arg[1],'!')+1);
                                $arg = join(':',$arg);

                                //tách vùng e.g: E3:G3 => E3,F3,G3
                                $arg =  Coordinate::extractAllCellReferencesInRange($arg);
                                $arg = join('*',$arg);

                                $arguments[$key] = $arg;
                            }
                        }
                        $arguments = join('*',$arguments);
                        $formula = str_replace($matches[0],$arguments,$formula);
                    }
                }
                $parseExpression = $this->parseExpression($formula);
                return [$formula,$parseExpression,$flag];
            break;
            default:
                # code...
                break;
        }

        return $formula;
    }

    /**
     * validate range
     * E.g: sheet1!E3:E5 , sheet1!E3:sheet1!E5 => true, sheet1!E3 ,E3 =>false
     * @param string $range Vùng cần kiểm tra
     * @return true is range | false is not range
     * **/
    function validateRange($range){
        if(preg_match('/^' . Calculation::CALCULATION_REGEXP_CELLREF . ':' . Calculation::CALCULATION_REGEXP_CELLREF . '$/miu', $range) !== 1){
            return false;
        }
        return true;
    }

    /**
     * Xử lý vùng dữ liệu
     * E.g: 01!E3:01!D3 => 01!E3:D3
     * @param string $range Vùng cần xử lý
     * @return string $range
     * **/
    public function handlerRangeReferences3D($range){
        try {
            if(substr_count($range,'!') > 1){
                $exploded =  explode(':',$range);
                $addressA =  Worksheet\Worksheet::extractSheetTitle($exploded[0],true);
                $addressB =  Worksheet\Worksheet::extractSheetTitle($exploded[1],true);
                if($addressA[0] == $addressB[0]){
                    $range = "{$addressA[0]}!{$addressA[1]}:{$addressB[1]}";
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        return $range;
    }

    /**
     * Xóa dấu câu trong chuỗi
     * @param string $str Chuỗi cần xóa dấu
     * @return string $str
     * **/
    function stripUnicode($str){
        $unicode = array(
        'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd'=>'đ',
        'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i'=>'í|ì|ỉ|ĩ|ị',
        'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
        );
        foreach($unicode as $nonUnicode=>$uni) $str = preg_replace("/($uni)/i",$nonUnicode,$str);

        $str = str_replace(' ','',mb_strtolower($str));
        return $str;
    }

    /**
     * Tách nhỏ công thức
     * @param string $formula Công thức
     * @return array
     * **/
    function parseExpression($formula){
        // $formula = str_replace(' ','',$formula);
        //tìm các biểu thức dạng lũy thừa E.g: (A+B*D)^2
        $argsExpo = $this->findExponents($formula);
        $formula = $argsExpo[0];
        //mảng chứa các phép lũy thừa của hàm gốc
        $argsExpo = $argsExpo[1];

        //thay thế tạm thời tên sheet
        //tránh lỗi E.g: sheet name is: SBD01-MSV705906042
        $pattern = "/\'(.*?)\'/";
        $sheets = [];
        if(preg_match_all($pattern, $formula, $matches)){
            $sheets = $matches[0];
            foreach ($sheets as $key => $sheet) {
                $formula = str_replace($sheet,"'sheet{$key}'",$formula);
            }
        }

        //convert phép tính theo thứ tự /,*,-
        $formula = $this->convertDivison($formula);
        $formula = $this->convertMultiplication($formula);
        $formula = $this->convertSub($formula);

        //gắn lại tên sheet
        foreach ($sheets as $key => $sheet) {
            $formula = str_replace("'sheet{$key}'",$sheet,$formula);
        }

        $formula = str_replace(['(', ')','='],'',$formula);
        $argsGoc = explode('+',$formula);

        $argsMultiplication = []; //mảng chứa các phần tử của phép nhân
        $argsSum = []; //mảng chứa các số hạng của phép cộng


        for ($i=0; $i <count($argsGoc) ; $i++) {
            if(str_contains($argsGoc[$i],'*')){
                array_push($argsMultiplication, ...explode('*',$argsGoc[$i]));
            }
            else{
                array_push($argsSum,$argsGoc[$i]);
            }
        }
        $findArgsGoc = $this->findAndMove($argsMultiplication,$argsSum,$argsExpo);
        return $findArgsGoc;
    }

    /**
     * Xử lý độ lệch vùng
     * @param array $listTitleData Mảng chứa thông tin header của file tư liệu
     * @param array $listTitleExam Mảng chứa thông tin hàng đọc từ sheet cần so sánh
     * @param int $rowIndexExam Vị trí hàng
     * @return bool true nếu trùng với hàng header của file tư liệu | false nếu ko
     * **/
    public function handleDeviation($listTitleData,$listTitleExam,$rowIndexExam){
        $array_intersect =  count(array_intersect($listTitleExam, $listTitleData[2]));
        //trùng tối thiểu 80%
        //E.g: header tư liệu là Họ và tên,Quê quán,Loại phòng,Giá phòng,Số ngày
        //thì header bài thi phải chứa tối thiểu 3/5:Họ và tên,Quê quán,Loại phòng
        if($array_intersect >= count($listTitleData[2]) - 3){
            //tiến hành so khớp với file tư liệu
            foreach ($listTitleData[1] as $itemTitle) {
                $columnName = array_search($itemTitle->Title,$listTitleExam);
                $addressFileData = $itemTitle->CellAddress;
                $addressFileExam = $columnName ? "{$columnName}{$listTitleData[0]->RowReference}" : null;
                // trùng title và khác địa chỉ
                if($addressFileExam && $addressFileData != $addressFileExam){
                    $toRow = $rowIndexExam - $listTitleData[0]->RowReference;
                    $this->deviation[$itemTitle->ColumnName] = ['toColumn' => $columnName, 'toRow' => $toRow];
                }
            }
            return true;
        }
    }

    /**
     *  Tách hàm if
     *  @param string $formula Công thức
     *  @param int $index Vị trí thêm phần tử
     *  @return array $result Mảng các phần tử gồm [logic,valueiftrue,valueiffalse]
     *  nếu có nhiều if lồng thì [[logic,valueiftrue,valueiffalse],[logic,valueiftrue,valueiffalse]]
     * **/
    function splitIfStatement($formula,$result,$index){
        $parts = $this->getIfArgument($formula);
        if(!empty($parts)){
            $result[$index]['Logic'][] = $parts[0];
            try {
                if(isset($parts[1]) && stripos($parts[1],'IF') === false){
                    $result[$index]['valueIfTrue'] = preg_replace('/[\"\s]+/', '',mb_strtolower($parts[1]));
                }
                else if(isset($parts[1])){
                    $result[$index]['valueIfTrue'] = null;
                    $result = $this->splitIfStatement($parts[1],$result,$index+1);
                }
                if(isset($parts[2]) && stripos($parts[2],'IF') === false){
                    $result[$index]['valueIfFalse'] = preg_replace('/[\"\s]+/', '',mb_strtolower($parts[2]));
                }
                else if(isset($parts[2])){
                    $result[$index]['valueIfFalse'] = null;
                    $result = $this->splitIfStatement($parts[2],$result,$index+1);
                }
                else{
                    $result[$index]['valueIfFalse'] = null;
                }
            } catch (\Throwable $th) {
                //throw $th;
                dd($formula,$this->list);
            }
        }
        return $result;
    }

    /**
     *  So sánh 2 biểu thức
     *  @param array $operandMaster Mảng chứa các biểu thức gốc
     *  @param array  $operandBranch Mảng chứa các biểu thức nhánh
     *  @param bool $type false: so sánh nghịch đảo của gốc với nhánh E.g: gốc: E3>8 ,nhánh:E3<=8
     *  convert gốc về E3<=8 và so sánh, true thực hiện so sánh bình thường
     *  @param int $sheetIndex vị trí sheet đặt công thức
     *  @param string $cellAddress ô đặt công thức
     *  @return bool  true nếu đúng | false nếu sau
     * **/
    function checkIfCondition($operandMaster,$operandBranch,$sheetIndex,$cellAddress,$type){
        $pattern = '/(<>|<=|>=|=|<|>)/';
        //tách nhỏ đối số của biểu thức nhánh
        foreach ($operandBranch as $key => $itemOperandBranch) {
            if(count(preg_split($pattern,$itemOperandBranch, -1, PREG_SPLIT_DELIM_CAPTURE))>1){
                $resultBranch[] = $this->splitCondition($itemOperandBranch,$sheetIndex,$cellAddress);
                array_splice($operandBranch,$key,1,$resultBranch);
            }
        }

        if($type){
            //tách nhỏ đối số
            foreach ($operandMaster as $key => $itemOperandMaster) {
                if(count(preg_split($pattern,$itemOperandMaster, -1, PREG_SPLIT_DELIM_CAPTURE))>1){
                    $resultMaster[] = $this->splitCondition($itemOperandMaster,$sheetIndex,$cellAddress);
                    array_splice($operandMaster,$key,1,$resultMaster);
                }
            }
        }
        else{
            //convert biểu thức ngược và tách nhỏ
            foreach ($operandMaster as $key => $itemOperandMaster) {
                $resultMaster[] = $this->reverseCondition($itemOperandMaster);
                array_splice($operandMaster,$key,1,$resultMaster);
            }
        }

        //so sánh biểu thức
        foreach ($operandBranch as $key => $value) {
            if(is_array($value) && $value[1] == '=' ||  $value[1] == '<>'){
                $find = array_filter($operandMaster,function($item) use($value){
                    return $this->array_equal($item,$value);
                });
            }
            else{
                $find = array_filter($operandMaster,function($item) use($value){
                    return $item === $value;
                });
            }
            if(!$find){
                return false;
            }
        }

        return true;
    }

    /**
     * tìm kiếm các function trùng nhau trong mảng các dấu cộng,nhân,^ và đẩy
     * vào các mảng tương ứng
     *
     * **/
    function findAndMove($argsMul,$argsSum,$argsExpo){
        $countMul =  array_count_values($argsMul);
        foreach ($countMul as $key => $value) {
            if($value>1){
                $argsExpo[] = [$value,$key];
            }
        }
        $argsMul = array_keys(array_filter(array_count_values($argsMul), function($count) { return $count == 1; })); //xóa các phần tử trùng từ 2 giá trị trở lên

        $countSum = array_count_values($argsSum);
        foreach ($countSum as $key => $value) {
            if($value>1){
                array_push($argsMul,$key,$value);
            }
        }
        $argsSum = array_keys(array_filter(array_count_values($argsSum), function($count) { return $count == 1; })); //xóa các phần tử trùng từ 2 giá trị trở lên
        return ['*'=>$argsMul,'+'=>$argsSum,'^'=>$argsExpo];
    }


    /**
     * Convert địa chỉ nằm trong table về dạng cơ bản
     * @param string $structuredReference
     * @param array $arrTable Mảng table đọc được từ bài thi
     * @param string    $cellAddress địa chỉ cell lấy công thức
     * @return string $cellRange địa chỉ đã được convert E.g: Table1[Số ngày]=> sheet1!E5
     * **/
    function parseStructuredReference($structuredReference,$arrTable,$cellAddress){
        $tableName = substr($structuredReference,0,strpos($structuredReference,'['));  //lấy ra tên table
        $cell = $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getCell($cellAddress);
        $cellRange = (new StructuredReference($structuredReference))->parse($cell);  //thực hiện convert
        $title = $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getTitle();
        $cellRange= "'{$title}'!{$cellRange}";
        return $cellRange;
    }

    /**
     * Tách các đối số biểu thức điều kiện
     * @param string $condition Biểu thức cần tách
     * @param int $sheetIndex vị trí sheet đặt công thức
     * @param string $cellAddress địa chỉ đặt công thức
     * @return array $result Mảng các đối số E.g: E3>4=> [4,'<',E3]
     * **/
    function splitCondition($condition,$sheetIndex,$cellAddress){
        $condition = str_replace("'",'',$condition);
        $pattern = '/(<>|<=|>=|=|<|>)/';
        $split =  preg_split($pattern,$condition, -1, PREG_SPLIT_DELIM_CAPTURE);
        //trường hợp đối số là vùng
        //e.g: E3:E25>=6
        if($this->validateRange($split[0])){
            try {
                $parseRangetoCellAddress = $this->parseRangetoCellAddress($split[0],$sheetIndex,$cellAddress);
            } catch (\Throwable $th) {
                dd($split[0]);
            }
            if($parseRangetoCellAddress[0]){
                $split[0] = $parseRangetoCellAddress[1];
            }
        }
        else if($this->validateRange($split[2])){
            $parseRangetoCellAddress = $this->parseRangetoCellAddress($split[2],$sheetIndex,$cellAddress);
            if($parseRangetoCellAddress[0]){
                $split[2] = $parseRangetoCellAddress[1];
            }
        }

        if($split[1] === '>'){
            $result = [$split[2],'<',$split[0]];
        }
        else if($split[1] === '<'){
            $result = [$split[0],$split[1],$split[2]];
        }
        else if($split[1] === '>='){
            $result = [$split[2],'<=',$split[0]];
        }
        else if($split[1] === '<='){
            $result = [$split[0],$split[1],$split[2]];
        }
        else if($split[1] === '<>' || $split[1] === '='){
            $result = [$split[0],$split[1],$split[2]];
        }
        else $result[] = $condition;
        return $result;
    }

    /**
     * Xây dựng biểu thức đối
     * @param string $condition biểu thức cần xây dựng
     * @return string $result biểu thức đối
     * E.g: E3>4 => 4<=E3 | E3<=4 => 4<E3
     * **/
    function reverseCondition($condition){
        $pattern = '/(<>|<=|>=|=|<|>)/';
        $split =  preg_split($pattern,$condition, -1, PREG_SPLIT_DELIM_CAPTURE);
        if($split[1] === '>'){
            $result = [$split[0],'<=',$split[2]];
        }
        else if($split[1] === '<'){
            $result = [$split[2],'<=',$split[0]];
        }
        else if($split[1] === '>='){
            $result = [$split[0],'<',$split[2]];
        }
        else if($split[1] === '<='){
            $result = [$split[2],'<',$split[0]];
        }
        else if($split[1] === '<>'){
            $result = [$split[0],'=',$split[2]];
        }
        else if($split[1] === '='){
            $result = [$split[0],'<>',$split[2]];
        }
        else $result[] = $condition;

        return $result;
    }

    /**
     * Lấy ra các đối số trong hàm (SUM,MAX,MIN...)
     * @param string $formula
     * @return array Return mảng các đối số
     * **/
    function getArgumentsFormula($formula) {
        preg_match('#\((.*?)\)#', $formula, $match);
        $formula= $match[1];
        $result = explode(",", $formula);
        foreach($result as $key=>$value){
            if(strpos($value, "[[#") !== false){
                $result[$key] .= ','.$result[$key+1];
                unset($result[$key+1]);
            }
        }
        return $result;
    }

    /**
     * Hàm convert phép nhân (E.g: (A+B)*C => AC+BC or (A+B)*(C+D)=> AC+AD+BC+BD)
     *
     * **/
    function convertMultiplication($formula,$position = null){
        if(!$position){
            $position =  strpos($formula,'*');
            while($position && ($formula[$position+1] !== '(' &&  $formula[$position-1] !== ')')){
                $position = strpos($formula,'*',$position+1);
            }
        }
        if($position){
            $firstTerm = $this->getFirstTerm($formula,$position);   //tham số 1
            $lastTerm = $this->getLastTerm($formula,$position);     //tham số 2
            $search = $firstTerm.'*'.$lastTerm;


            $lastTerm = str_replace('-','+-',$lastTerm);
            $firstTerm = str_replace('-','+-',$firstTerm);

            //mảng chứa các tham số vế trái
            $argsFirst = explode('+',str_replace(['(', ')'],'',$firstTerm));
            //mảng chứa các tham số vế phải
            $argsLast = explode('+',str_replace(['(', ')'],'',$lastTerm));


            //mảng chứa các tham số của tổng các tích
            $args = [];

            for ($i=0; $i <count($argsFirst) ; $i++) {
                for ($j=0; $j <count($argsLast) ; $j++) {
                    $args[] = $argsFirst[$i].'*'.$argsLast[$j];
                }
            }
            $replace = join('+',$args);
            $formula = str_replace($search,$replace,$formula);
        }

        //kiểm tra xem còn phép nhân với biểu thức nào ko (E.g: A*(C+D))
        $position =  strpos($formula,'*',$position+1);
        while($position && ($formula[$position+1] !== '(' ||  $formula[$position-1] !== ')')){
            $position = strpos($formula,'*',$position+1);
        }
        if($position){
            $formula = $this->convertMultiplication($formula,$position);
        }
        return $formula;
    }

      /**
     * Convert các phép trừ thành phép cộng
     * trong công thức
     * **/
    private function convertSub($formula,$position = null){
        if(!$position){
            $position =  strpos($formula,'-');
            while($position && ($formula[$position-1] === '*' || $formula[$position-1] === '^' ||  $formula[$position-1] === '+')){
                $position = strpos($formula,'-',$position+1);

            }
        }
        if($position){
            $firstTerm = $this->getFirstTerm($formula,$position);
            $lastTerm = $this->getLastTerm($formula,$position);
            $search =  $firstTerm.'-'.$lastTerm;
            if($formula[$position+1] === '('){                 //là biểu thức 1 (A+B)
                preg_match('/\((.*)\)/', $lastTerm, $matches);
                $replace = $matches[1];

                //tìm vị trí các dấu nằm ngoài ngoặc
                $paren_count = 0;
                $outside_paren = array();
                for ($i=0; $i<strlen($replace); $i++) {
                    if ($replace[$i] == '(') {
                        $paren_count++;
                    } elseif ($replace[$i] == ')') {
                        $paren_count--;
                    } elseif (in_array($replace[$i], array('+', '-', '*')) && $paren_count == 0) {
                        $outside_paren[] = $i;
                    }
                }

                for ($i=0; $i <count($outside_paren) ; $i++) {
                    if($replace[$outside_paren[$i]] === '-'){
                        $replace = substr_replace($replace,'+',$outside_paren[$i],1);
                    }
                    else if($replace[$outside_paren[$i]] === '+'){
                        $replace = substr_replace($replace,'-',$outside_paren[$i],1);
                    }
                }
                $formula = str_replace($lastTerm,$replace,$formula);

            }


            $formula = substr_replace($formula,'+-',$position,1);

            //tìm kiếm xem trong chuỗi còn dấu - nào ko
            $position =  strpos($formula,'-',$position+1);
            while($position && ($formula[$position-1] === '*' || $formula[$position-1] === '^' ||  $formula[$position-1] === '+')){
                $position = strpos($formula,'-',$position+1);
            }
            if($position){
                $formula = $this->convertSub($formula,$position);
            }

        }

        return $formula;

    }

    /**
     * Thực hiện chuyển đổi các phép chia
     * trong công thức thành phép nhân
     * **/
    private function convertDivison($string){
        $position = strpos($string, '/');
        $search = '';    //phép chia cần thay thế
        $replace = '';   //phép chia thay thế
        $flag = false;
        //chuỗi có phép chia
        if($position){
            $arr = [];
            //tìm số chia
            //trước phép chia là 1 biểu thức khác ví dụ (A+B)/C

            //số chia
            $firstTerm = $this->getFirstTerm($string,$position);

            //số bị chia
            $lastTerm = $this->getLastTerm($string,$position);

            $search = $firstTerm.'/'.$lastTerm;  //phép chia cần thay thế

            //kiểm tra các trường hợp số bị chia chứa biểu thức (Eg: A/(B*C) or A/(B+C))
            //trường hợp chia cho số mũ
            if(str_contains($lastTerm,'^')){
                $replace = substr($lastTerm,1,strlen($lastTerm)-2);
                //số mũ
                $exponential = explode('^',$replace)[1];
                if(is_numeric($exponential)){
                    (int)$exponential *= -1;
                }
                else{
                    $exponential = '-'.$exponential;
                }
                //cơ số
                $number = explode('^',$replace)[0];
                $replace = $firstTerm.'*'.$number.'^'.$exponential;
                $string = str_replace($search,$replace,$string);
            }
            //chia cho phép nhân
            else if(str_contains($lastTerm,'*')){
                $replace = substr($lastTerm,1,strlen($lastTerm)-1);  //A/(B*C)  =>xóa dấu ngoặc ( ở (B*C)
                $replace = str_replace('*','^-1*',$replace);  //đỏi phép nhân trong B*C thành B^-1*C
                $replace = str_replace(')','^-1',$replace);
                $replace = $firstTerm.'*'.$replace;
                $string = str_replace($search,$replace,$string);
            }
            //chia cho phép trừ
            else{
                $replace = $firstTerm.'*'.$lastTerm.'^-1';
                $string = str_replace($search,$replace,$string);
            }
            //kiểm tra xem còn dấu chia trong chuỗi ko
            if(str_contains($string,'/')){
                $string = $this->convertDivison($string);
            }
        }


        return $string;
    }

    /**
     * Tìm các phép lũy thừa dạng biểu thức
     * **/
    public function findExponents($expression){
        $exp = [];
        $index = 0;
        while ($position = strpos($expression,')^')) {
            $firstTerm = $this->getFirstTerm($expression,$position+1);
            $lastTerm = $this->getLastTerm($expression,$position+1);
            $search = $firstTerm.'^'.$lastTerm;
            $exp[] = $search;
            $expression = str_replace($search,'t'.$index,$expression);
            $index++;
        }
        return [$expression,$exp];
    }

     /**
     * lấy ra số hạng đầu trong công thức
     *  @param string $formula
     *  @param int $position Vị trí bắt đầu lấy
     *  @return array Return firstTerm and flag
     * **/
    private function getFirstTerm($string,$position){

        $result = [];

        $arr = [];
        if($string[$position-1] == ')'){
            $dem = 0;
            for ($i=$position - 1; $i>=0;$i--) {
                array_push($arr,$string[$i]);
                if($string[$i]==')'){
                    $dem++;
                }
                else if($string[$i]== '('){
                    $dem--;
                    if($dem == 0) break;
                }
            }
        }
        else{
            // dd($string[$position],$string);
            $dem = 0;
            for ($i=$position-1; $i>=0;$i--) {
                array_push($arr,$string[$i]);
                if(($string[$i] === '-' && $string[$i-1] !=='^')){
                    array_pop($arr);
                    break;
                }
                else if($string[$i] === ')'){
                    $dem++;
                }
                else if($string[$i] === '('){
                    $dem--;
                    if($dem <= 0){
                        array_pop($arr);
                        break;
                    }
                }
                else if(in_array($string[$i],['*','/','+','(',')','=']) && $dem == 0){
                    break;
                }

            }
        }
        $arr = array_reverse($arr);

        $firstTerm = join('',$arr);
        return $firstTerm;
    }


    /**
     * lấy ra số hạng cuối trong công thức
     *  @param string $formula
     *  @param int $position Vị trí bắt đầu lấy
     *  @return string Return lastTerm
     * **/
    private function getLastTerm($string,$position){
        $arr = [];
        //tìm số bị chia
        //sau phép chia là biểu thức
        if($string[$position+1] == '('){
            $dem = 0;
            for ($i=$position+1; $i < strlen($string);$i++) {
                array_push($arr,$string[$i]);
                if($string[$i]=='('){
                    $dem++;
                }
                else if($string[$i]== ')'){
                    $dem--;
                    if($dem == 0) break;
                }
            }
        }
        //sau là địa chỉ
        else{
            for ($i=$position+1; $i <strlen($string) ; $i++) {
                if(!in_array($string[$i],['*','/','+','-','(',')'])){
                    array_push($arr,$string[$i]);
                }
                else if($string[$i-1]=='^'){                        //E.g   E^-2
                    array_push($arr,$string[$i]);
                }
                else break;
            }
        }

        return join('',$arr);
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
     * Convert vùng dữ liệu cho các công thức
     * @param string $range Vùng cần convert
     * @param array array table đọc được từ file bài thi
     * **/
    private function convertRange($range,$arrTable){
        if(str_contains($range,'[')){
            $parts = explode('[',$range);
            $tableName = $parts[0];
            if(substr_count($range,'[')>2){   //trường hợp NhaTrang[[#All],[Số ngày]]
                $tableColumn = str_replace(']', '', $parts[3]);
                //kiểm tra xem thuộc table nào
                if(array_key_exists($tableName, $arrTable)){
                    //kiểm tra xem thuộc column nào
                    if(array_key_exists($tableColumn,$arrTable[$tableName]['Column'])){
                        $title = $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getTitle();
                        $columnName = $arrTable[$tableName]['Column'][$tableColumn]->ColumnName;
                        // $range = "'".$title."'!".$columnName.$arrTable[$tableName]['RangeStart'].':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                        $range = $columnName.$arrTable[$tableName]['RangeStart'].':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                    }
                }
            }
            else{                              // NhaTrang[Số ngày]
                $tableColumn = str_replace(']', '', $parts[1]);
                if(array_key_exists($tableName, $arrTable)){
                    //kiểm tra xem thuộc column nào
                    if(array_key_exists($tableColumn,$arrTable[$tableName]['Column'])){
                        $title = $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getTitle();
                        $columnName = $arrTable[$tableName]['Column'][$tableColumn]->ColumnName;
                        // $range = $title.'!'.$columnName.($arrTable[$tableName]['RangeStart']+1).':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                        $range = $columnName.($arrTable[$tableName]['RangeStart']+1).':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                    }
                }
            }
        }
        return $range;
    }

    /**
     * Lấy ra các đối số của hàm if
     * @param string $formula Công thức cần lấy đối số
     * @return array [logic,valueifTrue,valueifFalse]
     * **/
    function getIfArgument($formula){
        $stack = [];
        if(preg_match('/\((.*)\)/', $formula, $matches)){
            $formula = $matches[1];
            $buffer = '';
            $depth = 0;
            $len = strlen($formula);
            for ($i=0; $i<$len; $i++) {
                $char = $formula[$i];
                switch ($char) {
                case '(':
                    $depth++;
                    break;
                case ',':
                    if (!$depth) {
                        if ($buffer !== '') {
                            $stack[] = $buffer;
                            $buffer = '';
                        }
                        continue 2;
                    }
                    break;
                case ' ':
                    if (!$depth) {
                        continue 2;
                    }
                    break;
                case ')':
                    if ($depth) {
                        $depth--;
                    } else {
                        $stack[] = $buffer.$char;
                        $buffer = '';
                        continue 2;
                    }
                    break;
                }
                $buffer .= $char;
            }
            if ($buffer !== '') {
                $stack[] = $buffer;
            }
        }
        return $stack;
    }


    /**
     * Kiểm tra merge cell
     * @param int $id_sheet vị trí sheet
     * @param string $startCell vị trí đầu
     * @param string $endCell vị trí cuối
     * @return bool true | false
     * **/
    public function MergeCells($id_sheet,$range){
        if($this->sheetExists($id_sheet)){
            if(array_key_exists($range,$this->spreadsheet->getActiveSheet()->getMergeCells())){
                return true;
            }
        }
        return false;

    }

    /**
     *  Kiểm tra định dạng font chữ
     *  @param int $id_sheet vị trí sheet cần định dạng
     *  @param string $range vùng cần định dạng
     *  @param int $type loại định dạng
     *  @return bool true | faslse
     * **/
    public function FontStyle($id_sheet,$range,$type){
        if($this->sheetExists($id_sheet)){
            $ranges = Coordinate::extractAllCellReferencesInRange($range);
            $worksheet = $this->spreadsheet->getSheet($id_sheet);
            switch ($type) {
                case \App\Enums\FontStyle::Regular:

                    break;
                case \App\Enums\FontStyle::Italic:
                    foreach ($ranges as $key => $range) {
                        if(!$worksheet->getCell($range)->getStyle()->getFont()->getItalic()){
                        }
                    }
                    break;
                case \App\Enums\FontStyle::Bold:
                    foreach ($ranges as $key => $range) {
                        if(!$worksheet->getCell($range)->getStyle()->getFont()->getSharedComponent()->getBold()){
                        }
                    }
                    break;
                case \App\Enums\FontStyle::BoldItalic:
                    break;
                case \App\Enums\FontStyle::Title:
                    $listRange = Coordinate::extractAllCellReferencesInRange($range);
                    $COLOR = FontColorDefault::Title;
                    //kiểm tra kích thước và màu sắc
                    foreach ($listRange as $key => $range) {
                        $style = $worksheet->getStyle($range);
                        $color = $style->getFont()->getColor()->getArgb();
                        $fontSize = $style->getFont()->getSize();
                        if($color != $COLOR && $fontSize != 18){
                        }
                    }
                default:
                    # code...
                    break;
            }
            return true;
        }
    }

    /**
     *  Kiểm tra sự tồn tại của sheet với vị trí chỉ định
     *  @param int $id_sheet Vị trí sheet
     *  @return bool true: tồn tại|false: ko tồn tại
     * **/
    public function sheetExists($id_sheet){
        return $id_sheet>=0 && $id_sheet<$this->sheetCount;
    }


    /**
     * Kiểm tra tên sheet
     * **/
    function sheetName($id_sheet,$sheetName){
        if($this->sheetExists($id_sheet)){
            if($this->spreadsheet->getSheet($id_sheet)->getTitle() === $sheetName){
                return true;
            }
        }
    }

    /**
     * Kiểm tra font size
     * **/
    function FontSize($range,$size){
        if($this->sheetExists($id_sheet)){
            if($this->spreadsheet->getSheet($id_sheet)->getTitle() === $sheetName){
                return true;
            }
        }
    }

    /**
     *  kiểm tra hướng giấy
     *
     * **/
    public function PageOrientation($id_sheet,$type){
        if($this->sheetExists($id_sheet)){
            if($this->spreadsheet->getSheet((int)$id_sheet)->getPageSetup()->getOrientation() == strtolower(\App\Enums\PageOrientation::getKey((int)$type))){
                return true;
            }
        }
        return false;
    }

    /**
     * Kiểm tra chiều
     * **/
    function RowHeight($id_sheet,$height){

    }

    /**
     * Kiểm tra khổ giấy
     * **/
    function PageSize($id_sheet,$typeSize){
        if($this->sheetExists($id_sheet)){
            $pageSize = $this->spreadsheet->getSheet((int)$id_sheet)->getPageSetup()->getPaperSize();
            switch ($typeSize) {
                case \App\Enums\PageSize::Letter:
                    if($pageSize === 1 || $pageSize === 2){
                        return true;
                    }
                case \App\Enums\PageSize::Tabloid:
                    if($pageSize === 3){
                        return true;
                    }
                case \App\Enums\PageSize::Legal:
                    if($pageSize === 5){
                        return true;
                    }
                case \App\Enums\PageSize::Statement:
                    if($pageSize === 6){
                        return true;
                    }
                case \App\Enums\PageSize::Executive:
                    if($pageSize === 7){
                        return true;
                    }
                case \App\Enums\PageSize::A3:
                    if($pageSize === 8){
                        return true;
                    }
                case \App\Enums\PageSize::A4:
                    if($pageSize === 9 || $pageSize === 10){
                        return true;
                    }
                    break;
                case \App\Enums\PageSize::B4:
                    if($pageSize === 12){
                        return true;
                    }
                case \App\Enums\PageSize::B5:
                    if($pageSize === 13){
                        return true;
                    }
            }
        }
    }

    /**
     * Kiểm tra font chứ
    */
    function verifyFont($criteria){
        if($this->sheetExists($criteria->SheetId)){
            $fontName =  $this->spreadsheet->getSheet($criteria->SheetId)->getCell($criteria->Range)->getStyle()->getFont()->getName();
            if(str_replace(' ','',$fontName) !== Font::getKey((int)$criteria->Content)){
                return true;
            }
        }
        return false;
    }

    /**
     * Kiểm tra font chữ toàn bộ trang
     */
    function verifyAllFont($criteria){
        if($this->sheetExists($criteria->SheetId)){
            $worksheet = $this->spreadsheet->getSheet($criteria->SheetId);
            $highestDataRow = $worksheet->getHighestDataRow();
            $highestDataColumn = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());
            $fontName =  $this->spreadsheet->getSheet($criteria->SheetId)->getCellByColumnAndRow($highestDataColumn-1,$highestDataRow+1)->getStyle()->getFont()->getName();
            if(str_replace(' ','',$fontName) !== Font::getKey((int)$criteria->Content)){
                return true;
            }
        }
        return false;
    }

    /**
     * Kiểm tra font chữ tại 1 vùng
     */
    function verifyFontSize($criteria){
        if($this->sheetExists($criteria->SheetId)){
            $fontSize =  $this->spreadsheet->getSheet($criteria->SheetId)->getCell($criteria->Range)->getStyle()->getFont()->getSize();
            if($fontSize == $criteria->Content){
                return true;
            }
        }
        return false;
    }

    /**
     * Kiểm tra font size toàn bộ trang
     */
    function verifyAllFontSize($criteria){
        if($this->sheetExists($criteria->SheetId)){
            $worksheet = $this->spreadsheet->getSheet($criteria->SheetId);
            $highestDataRow = $worksheet->getHighestDataRow();
            $highestDataColumn = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());
            $fontSize =  $this->spreadsheet->getSheet($criteria->SheetId)->getCellByColumnAndRow($highestDataColumn-1,$highestDataRow+1)->getStyle()->getFont()->getSize();
            if($fontSize == $criteria->Content){
                return true;
            }
        }
        return false;
    }

    function ColumnWidth($SheetId,$column,$width){
        if($this->sheetExists($SheetId)){
            return true;
            $column = Coordinate::coordinateFromString($column)[0];
            dd(round($this->spreadsheet->getSheet($SheetId)->getColumnDimensions()[$column]->getWidth(),2),floor($this->spreadsheet->getSheet($SheetId)->getColumnDimensions()[$column]->getWidth('cm')));
            // $spreadsheet->getSheetByName('KQ')->getColumnDimensions()["C"]->getWidth()
        }
    }

    /**
     * kiểm tra merge cell center
     * **/
    public function HorizontalCenter($sheetIndex,$startCell,$endCell){
        if($this->spreadsheet->getSheet($sheetIndex)->getStyle($startCell)->getAlignment()->getHorizontal() == "center"){
            return true;
        }
    }

}
