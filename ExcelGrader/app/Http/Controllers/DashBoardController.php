<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $results = DB::select('(SELECT COUNT(*) AS total, NULL AS thang_565, NULL AS thang_658, NULL AS thang_809, NULL AS thang_910 FROM exambank e WHERE e.ExamBankId  NOT IN (SELECT c.ExamBankId FROM criteria c))
        UNION
        (SELECT  COUNT(*), NULL, NULL, NULL, NULL FROM exambank e1 )
        UNION
        (SELECT  NULL,COUNT(DISTINCT e.ExamBankId), NULL, NULL, NULL FROM examshiftdetail e)
        UNION
        (SELECT  NULL,COUNT(*), NULL, NULL, NULL FROM exam e)
        UNION
        (SELECT COUNT(*), NULL, NULL, NULL, NULL FROM users u)
        UNION
        (SELECT
          COUNT(CASE WHEN e.TotalScore BETWEEN 0 AND 5 THEN 1 END) AS thang_005,
          COUNT(CASE WHEN e.TotalScore BETWEEN 5 AND 6.5 THEN 1 END) AS thang_565,
          COUNT(CASE WHEN e.TotalScore BETWEEN 6.5 AND 8 THEN 1 END) AS thang_658,
          COUNT(CASE WHEN e.TotalScore BETWEEN 8 AND 9 THEN 1 END) AS thang_809,
          COUNT(CASE WHEN e.TotalScore BETWEEN 9 AND 10 THEN 1 END) AS thang_910
        FROM examresult e)
        UNION
        (SELECT COUNT(*), NULL, NULL, NULL, NULL FROM department d)');
        return ['ExamBankNoUse' => $results[0]->total, 'ExamBank' => $results[1]->total, 'ExamBankUse' => $results[2]->thang_565, 'Exam' => $results[3]->thang_565, 'User' => $results[4]->total, 'Score005' =>  $results[5]->total,
            'Score565' => $results[5]->thang_565, 'Score658' => $results[5]->thang_658, 'Score809' => $results[5]->thang_809, 'Score910' => $results[5]->thang_910, 'Department' => $results[6]->total
        ];
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
        //
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
