<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\department;
use Illuminate\Support\Facades\DB;
class DepartmentController extends Controller
{
    private $department;
    public function __construct(){
        $this->department = new department();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DB::table('department')
             ->select('department.*', DB::raw('IF(examshiftdetail.DepartmentId IS NOT NULL, true, false) AS is_exist'))
             ->leftJoin('examshiftdetail', 'department.DepartmentId', '=', 'examshiftdetail.DepartmentId')
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
        $attribute = $request->validate([
            'DepartmentName' => 'required',
            'DepartmentCode' => 'required|unique:department,DepartmentCode'
        ],
        [
            'DepartmentName.required' => 'Tên phòng thi không được để trống',
            'DepartmentCode.required' => 'Mã phòng thi không được để trống',
            'DepartmentCode.unique' => 'Mã phòng thi đã tồn tại',
        ]);

        department::insert($attribute);
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
        $attribute = $request->validate([
            'DepartmentName' => 'required',
            'DepartmentCode' => "required|unique:department,DepartmentCode,{$request->DepartmentId},DepartmentId",
        ],
        [
            'DepartmentName.required' => 'Tên phòng thi không được để trống',
            'DepartmentCode.required' => 'Mã phòng thi không được để trống',
            'DepartmentCode.unique' => 'Mã phòng thi đã tồn tại',
        ]);
        department::where('DepartmentId', $request->DepartmentId)->update($attribute);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        department::where('DepartmentId', $id)->delete();
    }
}
