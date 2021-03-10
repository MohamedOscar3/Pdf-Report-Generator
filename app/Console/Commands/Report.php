<?php

namespace App\Console\Commands;
use App\Account;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class Report extends Command
{
    protected $signature = 'report:generate {order?}';

    protected $description = 'Generate Reports';

    public function handle()
    {

        $query = Account::select('account_id','account_name')->withCount('tests');

        $order = $this->argument('order');
        if (!is_null($order) &&  strtolower($order) === 'desc') {
            $query->orderBy('tests_count',$order);
        } else {
            $query->orderBy('tests_count','asc');
        }

        $tests_for_account = $query->get();
        $generated_content = "var dd = {
            content: [
                {text: 'Summery Tests For Every Account', style: 'header',alignment:'center',margin:5},
                {
                    table: {

                        body: [
                            ['Account ID','Account Name', 'Tests Count'],
                            {report_data}

                        ]
                    },
                    alignment:'center',
                },
            ],
            defaultStyle : {
                alignment:'center',
            }
        }";

        $account_data = "";
        foreach ($tests_for_account as $item) {
            $account_data .= "\n[\"$item->account_id\",\"$item->account_name\",\"$item->tests_count\"],";
        }

        $generated_content = str_replace("{report_data}",$account_data,$generated_content);
        $report_name = "Report_".Carbon::now()->format('y-m-d_h-i-s').'.js';
        Storage::put($report_name,$generated_content);

        echo "Report Generated Successfully\n";
        echo "Report Title :" . $report_name;
    }
}
