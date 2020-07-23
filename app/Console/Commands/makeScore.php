<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class MakeScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MakeScore:make {examin_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to correct the score';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $examin_id = $this->argument('examin_id');
        if (!$examin_id) {
            return "需要考试id";
            exit(2);
        }
        $stu_answer_infos = DB::table('user_answers')->where('examin_id', $examin_id)->get();
        $examin_info = DB::table('examin')->where('id', $examin_id)->get('answers')->first();

        foreach ($stu_answer_infos as $stu_answer_info) {
            $s = json_decode($stu_answer_info->answer, true);
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
                }
            }
            print $stu_answer_info->stu_id." ".$score."\n\r";
            DB::table('user_answers')->where('examin_id', $examin_id)->where('stu_id', $stu_answer_info->stu_id)->
            update(['score' => $score]);
        }
    }
}
