<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


class ExaminController extends Controller
{
    /**
     * 展示给定用户的信息。
     *
     * @param  int  $id
     * @return Response
     */
    public function create(Request $request)
    {
        $examin_name = $request['examin_name'];
        $answers = strtoupper($request['answers']);
        $answers = json_encode(explode("\r\n", $answers));
        $startdate = $request['startdate'];
        $enddate = $request['enddate'];
        $res = DB::table('examin')->insert(["examin_name" => $examin_name, "startdate" => $startdate, "enddate" => $enddate, "answers" => $answers]);
        if ($res) {
            return view('examin.create', ['commit' => $res]);
        }
    }

    public function edit(Request $request, $id)
    {
        $commit = null;
        if ($request['_method'] == 'POST') {
            $examin_name = $request['examin_name'];
            $answers = strtoupper($request['answers']);
            $answers = json_encode(explode("\r\n", $answers));
            $startdate = $request['startdate'];
            $enddate = $request['enddate'];
            $examin_id = $request['id'];
            $res = DB::table('examin')->where("id", $id)->update(
                ["examin_name" => $examin_name, "startdate" => $startdate, "enddate" => $enddate, "answers" => $answers]
            );
        }
        $examin_info = DB::table('examin')->where('id', $id)->first();
        if ($examin_info) {
            $examin_info->answers = join("\r\n", json_decode($examin_info->answers, true));
        }
        return view('examin.edit', ["examin_info" => $examin_info, "commit" => $commit]);
    }

    public function index()
    {
        $examins = DB::table("examin")->get();
        return view("examin.index", ['examins' => $examins]);
    }

    public function answerCard(Request $request, $examin_id)
    {
        $stu_answer_info = null;
        $stu_answer_info = DB::table('user_answers')->where('examin_id', $examin_id)->where('stu_id', session('stu_info')->id);
        $examin_info = DB::table('examin')->where('id', $examin_id)->first(); // 考试答案

        if ($request['_method'] == 'POST') {
            $s = json_decode($request['answerField'], true); // 学员答案
            $score = 0;
            foreach (json_decode($examin_info->answers) as $k => $v) {
                if (isset($s[$k + 1])) {
                    if (join("", $s[$k + 1]) === $v) {
                        //print "正确 正确答案：".$v.",学员答案".join("", $s[$k+1])."<br>";
                        $is_right = 1;
                        // 单选题 1分，多选题 1.5分
                        if (strlen($v) == 1) {
                            $score += 1;
                        } else {
                            $score += 1.5;
                        }
                    } else {
                        $is_right = 0;
                        //print "错误 正确答案：".$v.",学员答案".join("", $s[$k+1])."<br>";
                    }
                    DB::table('examin_history')->insert(
                        [
                            'stu_id' => session('stu_info')->id, 'examin_id' => $examin_id, 'right_answer' => $v,
                            'stu_answer' => join("", $s[$k + 1]), 'is_right' => $is_right, 'answer_dt' => date('Y-m-d H:i:s'), 'answer_no' => $k + 1
                        ]
                    );
                }
            }
            if (!$stu_answer_info->get()->isNotEmpty()) {
                DB::table("user_answers")->insert(
                    [
                        'examin_id' => $examin_id, 'stu_id' => session('stu_info')->id, 'answer' => $request['answerField'],
                        'commit_date' => date("Y-m-d H:i:s"), 'score' => $score
                    ]
                );
            } else {
                $stu_answer_info->update(['answer' => $request['answerField'], 'commit_date' => date("Y-m-d H:i:s"), 'score' => $score]);
            }
            return redirect(route('answercard', ['examin_id' => $examin_id]));
        }

        $right_answers = json_decode($examin_info->answers, true);
        $stu_answer_info = $stu_answer_info->first();
        if ($examin_info) {
            $examin_info->answers = join("\r\n", json_decode($examin_info->answers, true));
        }
        return view("examin.answerCard", ['examin_info' => $examin_info, 'right_answers' => $right_answers,
        'stu_answer_info' => $stu_answer_info, 'stu_info' => session('stu_info')]);
    }
    public function Students(Request $request, $examin_id)
    {
        $students = DB::table('examin_student')
        ->where('examin_id', '=', $examin_id)
        ->get(
            ['stu_name', 'stu_tel', 'stu_school', 'id']
        );
        return view('examin.students', ['students' => $students]);
    }
    public function StudentsAdd(Request $request, $examin_id)
    {
        $commit = null;
        if ($request['_method'] == 'POST') {
            foreach (explode("\r\n", $request['students']) as $line) {
                $stu_msgs = explode("	", $line);
                print_r($stu_msgs);
                DB::table('examin_student')->insert(['stu_name' => $stu_msgs[2], 'stu_school' => $stu_msgs[1], 'stu_tel' => $stu_msgs[0], 'examin_id' => $examin_id]);
            }
            return redirect(route("examin_stu", ['examin_id' => $examin_id]));
        }
        return view('examin.studentsAdd', ['commit' => $commit]);
    }

    public function StuLogin(Request $request, $examin_id)
    {
        $commit = null;
        $examin = DB::table('examin')->where('id', $examin_id)->get()->first();
        if (!$examin) {
            return "<script>alert('不存在的考试');history.back();</script>";
        }
        if ($request['_method'] == 'POST') {
            if ($examin) {
                if (time() < strtotime($examin->startdate) || time()>strtotime($examin->enddate)) {
                    return "<script>alert('不在考试时间内，请按通知时间进入考试');history.back();</script>";
                }
            } else {
                return "<script>alert('不存在的考试');history.back();</script>";
            }
            $stu_info = DB::table('examin_student')->where('stu_tel', $request['stu_tel'])->where('examin_id', $examin_id)->get();
            if ($stu_info->isNotEmpty()) {
                session(["stu_info" => $stu_info[0]]);
                return redirect(route("answercard", ['examin_id' => $examin_id]));
            } else {
                return redirect(route('stulogin', ['examin_id' => $examin_id]));
            }
        }
        return view('stu.login', ['examin_name'=>$examin->examin_name]);
    }

    public function Analyze(Request $request, $examin_id)
    {
        $examin = DB::table('examin')->where('id', $examin_id)->get()->first();
        // 成绩排名
        $tab1 = DB::table('user_answers')->leftJoin('examin_student', function($join){
            $join->on('user_answers.stu_id', '=', 'examin_student.id');
        })->where('user_answers.examin_id', $examin_id)->where('examin_student.examin_id', $examin_id)
        ->where('commit_date','>=', $examin->startdate)->where('commit_date','<=',$examin->enddate)
            ->orderByDesc('user_answers.score')
            ->get(['examin_student.stu_name', 'examin_student.stu_tel', 'examin_student.stu_school', 'user_answers.score']);
        //print_r($tab1);

        // 正确率 
        $tab2 = json_decode(DB::table('examin')->where('id', $examin_id)->get('answers')->first()->answers);
        $user_answers = DB::table('user_answers')->
        where('examin_id', $examin_id)->where('commit_date','>=', $examin->startdate)->where('commit_date','<=',$examin->enddate)
        ->get('answer');
        $tab2_res = [];
        foreach ($tab2 as $index => $item) {
            $tab2_res[$index + 1] = ['right_answer' => $item, 'right' => 0, 'wrong' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F'=>0];
            $isfind = false;
            foreach ($user_answers as $k => $user_answer) {
                $isfind = true;
                $answer = json_decode($user_answer->answer, true);
                foreach ($answer as $k=>$res) {
                    $res = join("", $res);
                    if ($k === $index + 1) {
                        if ($item == $res) {
                            $tab2_res[$index + 1]['right']++;
                        } else {
                            $tab2_res[$index + 1]['wrong']++;
                        }
                        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $letter) {
                            if (stripos($res, $letter) > -1) {
                                $tab2_res[$index + 1][$letter]++;
                            }
                        }
                    }
                }
            }
            if (!$isfind) {
                $tab2_res[$index + 1]['wrong']++;
            }
        }
        // 分数段 平均分 前10%平均分 前20%平均分 ... 前60%平均分
        $tab3 = [];
        $total_count = count($tab1);
        for ($i = 1; $i <= 10; $i++) {
            $pos = intval($i / 10 * $total_count);
            foreach ($tab1 as $k => $item) {
                if ($k > $pos) {
                    continue;
                }
                if (!isset($tab3[$i])) {
                    $tab3[$i] = ["score" => $item->score, 'count' => 1];
                } else {
                    $tab3[$i]['score'] += $item->score;
                    $tab3[$i]['count']++;
                }
            }
        }
        ksort($tab3);

        // 成绩分布
        $tab4 = []; // 11-20,21-30...141-150
        foreach ($tab1 as $item) {
            $i = intval($item->score / 10);
            if (!isset($tab4[$i])) {
                $tab4[$i] = 1;
            } else {
                $tab4[$i]++;
            }
        }
        ksort($tab4);
        return view("examin.analyze", ['tab1' => $tab1, 'tab3' => $tab3, 'tab4' => $tab4, 'tab2' => $tab2_res]);
    }
}
