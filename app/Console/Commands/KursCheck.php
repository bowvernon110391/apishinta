<?php

namespace App\Console\Commands;

use App\Kurs;
use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class KursCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kurs:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek data kurs terbaru dari BKF';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addArgument("valuta", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Kode valuta kurs yang ingin dicek', null);
        // $this->addArgument('tanggal', InputArgument::OPTIONAL, 'Tanggal Kurs yang dicek (Y-m-d)', date('Y-m-d'));
        $this->addOption('tanggal', 't|tgl|d|date', InputOption::VALUE_OPTIONAL, 'Tanggal berlaku Kurs (Y-m-d)', date('Y-m-d'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // grab data
        $valuta = $this->argument('valuta');
        $tanggal = $this->option('tanggal');

        $kurs = Kurs::query()
                ->when( $valuta && (is_array($valuta) && $valuta[0] !== '*'), function($q) use ($valuta) {
                    // echo "query by kode\n";
                    $q->kode($valuta);
                })
                ->when($tanggal, function ($q) use ($tanggal) {
                    $q->perTanggal($tanggal);
                })
                ->get()->toArray();
        
        $this->line("<info>data kurs [ <comment>". implode(", ", $valuta) ."</> ] per tanggal</> <comment>{$tanggal}</>:\n");
        // dd($kurs);

        if (!count($kurs)) {
            $this->line("<error>No data found</>");
            return;
        }

        $table = new Table($this->output);
        $table->setHeaders([
            'id',
            'kode_valas',
            'kurs_idr',
            'jenis',
            'tanggal_awal',
            'tanggal_akhir',
            'created_at',
            'updated_at',
        ]);
        // dd($this->options());
        $table->setRows($kurs);
        $table->render();
    }
}
