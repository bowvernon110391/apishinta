<?php

namespace App\Console\Commands;

use App\Kurs;
use Illuminate\Console\Command;
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
        //
        /* print_r($this->arguments());
        print_r($this->options()); */
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
        
        dump("Menampilkan data kurs [ ". implode(", ", $valuta) ." ] per tanggal {$tanggal}:\n");
        dump($kurs);
        // dd($this->options());
    }
}
