<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\examshiftdetail;
class ExamEvaluatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DB::select(DB::raw('
        SELECT DISTINCT JSON_ARRAYAGG(JSON_OBJECT("ExamShiftCode",t.ExamShiftCode,"ExamShiftDetailId",t.ExamShiftDetailId,"ExamBankId",t.ExamBankId, "ExamShiftId", t.ExamShiftId,"ResourcePathFileList",t.ResourcePathFileList,"ResourcePathFileAssignment",t.ResourcePathFileAssignment,"ResourcePath",t.ResourcePath,"ExamShiftName", t.ExamShiftName,"ExamBankName",t.ExamBankName,"DateTimeStart",t.DateTimeStart,"DepartmentName",t.DepartmentName,"DepartmentId",t.DepartmentId)) AS ExamShift, MAX(t.ExamId) AS ExamId, t.ExamCode, t.ExamStartDate, t.ExamEndDate, t.Note, t.ExamName
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

    /**
     * Store a newly created resource in storage.
     * Lấy kết quả chấm
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // SELECT  * FROM examresult e WHERE e.ExamShiftDetailId IN( SELECT DISTINCT e.ExamShiftDetailId FROM examshiftdetail e,examresult e1 WHERE e.DepartmentId = {$request['DepartmentId']} AND e.ExamShiftId= {$request['ExamShiftId']}) ORDER BY e.StudentCode
        return DB::select(DB::raw("SELECT  e.StudentCode,e.CandidateNumber,e.StudentName,e.TotalScore,e.Note,e.DepartmentName FROM examresult e WHERE e.ExamShiftDetailId IN( SELECT e.ExamShiftDetailId FROM examshiftdetail e,examresult e1 WHERE e.DepartmentId = {$request['DepartmentId']} AND e.ExamShiftId= {$request['ExamShiftId']})  GROUP BY e.CandidateNumber,e.StudentCode,e.CandidateNumber,e.StudentName,e.TotalScore,e.Note,e.DepartmentName ORDER BY e.StudentCode"));
    }

    public function uploadFile(Request $request){
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        if (!$receiver->isUploaded()) {
            // file not uploaded
        }
        $fileReceived = $receiver->receive(); // receive file
        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $file = $fileReceived->getFile(); // get file
            $new_name = $request->ExamId.'-'.$request->ExamShiftId.'-'.$request->DepartmentId;
            $attributes = [
                'ResourcePath' => "ExcelGrader/ExcelScore/{$request->ExamId}/{$new_name}",
            ];
            $find = DB::table('examshiftdetail')->where('DepartmentId',$request->DepartmentId)->where('ExamShiftId',$request->ExamShiftId);
            if(substr($file->getClientOriginalName(),0,3) === 'DS@'){
                //nếu đã tồn tại đường dẫn thì thực hiện xóa
                File::delete(public_path($find->get()->first()->ResourcePath.'/'.$find->get()->first()->ResourcePathFileList));
                $find->get()->first()->ResourcePath = $file->getClientOriginalName();
                $attributes['ResourcePathFileList'] = $file->getClientOriginalName();
            }
            else if(substr($file->getClientOriginalName(),0,3) === 'BT@'){
                File::delete(public_path($find->get()->first()->ResourcePath.'/'.$find->get()->first()->ResourcePathFileAssignment));
                $find->get()->first()->ResourcePath = $file->getClientOriginalName();
                $attributes['ResourcePathFileAssignment'] = $file->getClientOriginalName();
            }
            try {
                DB::beginTransaction();
                $find->update($attributes);
                $file->move(public_path()."/{$attributes['ResourcePath']}",$file->getClientOriginalName());
                DB::commit();
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    /**
     * Xuất dữ liệu chấm thi
     */
    public function exportExamResult(Request $request){
        try {
            // Khởi tạo đối tượng Spreadsheet
            $spreadsheet = IOFactory::load('ExcelGrader\ExportTeamplate\Kết quả chấm thi - teamplate.xlsx');
            $worksheet = $spreadsheet->getSheet(0);
            $worksheet->setTitle('Sheet1');
            $firstElement = $request->data[0];
            $worksheet->getCell('F5')->setValue($firstElement['DepartmentName']);
            // Thêm dữ liệu vào các dòng
            $row = 8;
            foreach ($request->data as $key => $item) {
                $worksheet->setCellValue('A' . $row, $key + 1);
                $worksheet->setCellValue('B' . $row, $item['CandidateNumber']);
                $worksheet->setCellValue('C' . $row, $item['StudentCode']);
                $worksheet->setCellValue('D' . $row, $item['StudentName']);
                $worksheet->setCellValue('E' . $row, $item['TotalScore']);
                $worksheet->setCellValue('F' . $row, $item['Note']);
                $worksheet->getRowDimension($row)->setRowHeight(20);
                $worksheet->getStyle("A{$row}:F{$row}")->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $worksheet->getStyle("A{$row}:F{$row}")->getBorders()->getOutline()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);
                $worksheet->getStyle("A{$row}:F{$row}")->getFont()
                ->setSize(12)
                ->setName('Times New Roman');
                $row++;
            }

            $worksheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            // Thiết lập kiểu file và tên file để xuất
            $writer = new Xlsx($spreadsheet);
            $filename = 'export_data.xlsx';

            // Thiết lập HTTP headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            // Ghi file Excel vào output
            $writer->save('php://output');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function exportExamList(Request $request){
        try {
            $spreadsheet = IOFactory::load("{$request->fileInfo['ResourcePath']}/{$request->fileInfo['ResourcePathFileList']}");
            $worksheet = $spreadsheet->getSheet(0);
            $worksheet->setTitle('Danh sách');
            //số lượng đề thi
            $count = count($request->exam);
            //dòng dữ liệu lớn nhất trong sheet
            $highestDataRow = $worksheet->getHighestDataRow();
            //thêm cột D để phân đề
            $worksheet->insertNewColumnBefore('D', 1);
            $worksheet->getColumnDimension('D')->setVisible(true);
            for ($i = 2; $i <= $highestDataRow ; $i++) {
                $studentID = trim($worksheet->getCell('B'.$i)->getValue());    //mã sinh viên
                $keyStudent = trim($worksheet->getCell('A'.$i)->getValue());   //số báo danh
                $studentName = trim($worksheet->getCell('C'.$i)->getValue());  //tên sinh viên
                $cell = "D{$i}";
                if(is_numeric($keyStudent) && is_numeric($studentID)){
                    //số lượng đề thi sử dụng
                    switch ((int)$count) {
                        //sử dụng 1 đề
                        case 1:
                            $worksheet->getCell($cell)->setValue($request->exam[0]['ExamBankName']);
                            break;
                        //sử dụng 2 đề
                        case 2:
                            if($keyStudent % 2 == 0){
                                $worksheet->getCell($cell)->setValue($request->exam[0]['ExamBankName']);
                            }
                            else{
                                $worksheet->getCell($cell)->setValue($request->exam[1]['ExamBankName']);
                            }
                            break;
                        //sử dụng 3 đề
                        case 3:
                            if($keyStudent % 3 == 0){
                                $worksheet->getCell($cell)->setValue($request->exam[2]['ExamBankName']);
                            }
                            else{
                                if($keyStudent % 2 == 0){
                                    $worksheet->getCell($cell)->setValue($request->exam[0]['ExamBankName']);
                                }
                                else{
                                    $worksheet->getCell($cell)->setValue($request->exam[1]['ExamBankName']);
                                }
                            }
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }
            // Thiết lập kiểu file và tên file để xuất
            $writer = new Xlsx($spreadsheet);
            $filename = 'export_data.xlsx';

            // Thiết lập HTTP headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            // Ghi file Excel vào output
            $writer->save('php://output');
        } catch (\Throwable $th) {
            //throw $th;
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
        return DB::table('examresultdetail')->where('StudentCode',$id)->get();
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
        //
    }
}
