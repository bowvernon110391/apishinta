<?php

namespace App\Console\Commands;

use App\Kurs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\Table;

class KursUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kurs:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update data kurs dengan menarik data kurs pajak di situs BKF';

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
        // just do what KursController::update does
        Log::info("kurs:update => pulling data from BKF...");
        try {
            $kursData = grabKursData();
            Log::info("kurs:update => data pulled...");
        } catch (\Throwable $e) {
            $this->line("<error>Error pulling kurs from BKF: {$e->getMessage()}</>\n");
            Log::error("kurs:update => [ERROR] {$e->getMessage()}");
            return false;
        }

        // keep track of these
        $inserted = 0;
        $updated = 0;

        // do we have it?
        if ($kursData) {
            Log::info("kurs:update => syncing kurs data with pulled data...");           
            
            DB::beginTransaction();

            try {
                // save for each kurs
                foreach ($kursData['data'] as $valas => $nilaiKurs) {
                    // try to search first
                    $k = Kurs::where('kode_valas', $valas)
                                ->where('jenis', 'KURS_PAJAK')
                                ->where('tanggal_awal', $kursData['dateStart'])
                                ->where('tanggal_akhir', $kursData['dateEnd'])
                                ->get();

                    // welp, duplicate found. let's replace it
                    if (count($k)) {
                        // replace every occurence (get returns collection)
                        foreach ($k as $kRep) {
                            $kRep->kurs_idr = $nilaiKurs;
                            $kRep->save();

                            $updated ++;
                        }
                        // $updated++;
                    } else {
                        // new shiet. save it
                        $k = new Kurs();
                        $k->kode_valas = $valas;
                        $k->jenis = 'KURS_PAJAK';
                        $k->tanggal_awal = trim($kursData['dateStart']);
                        $k->tanggal_akhir = trim($kursData['dateEnd']);
                        $k->kurs_idr = $nilaiKurs;

                        

                        // throw new \Exception(json_encode($k));

                        $k->save();

                        $inserted ++;
                    }
                }
                DB::commit();

                // show the info first
                $this->line("<info>Inserted:</> <comment>{$inserted}</>, <info>Updated:</> <comment>{$updated}</>");

                // show table?
                array_walk($kursData['data'], function (&$v, $k) use ($kursData) {
                    $v = [
                        'valuta' => $k,
                        'kurs_idr' => $v,
                        'tanggal_awal' => $kursData['dateStart'],
                        'tanggal_akhir' => $kursData['dateEnd'],
                    ];
                } );

                // dd($kursData['data']);
                $table = new Table($this->output);

                $table->setHeaders([
                    'Valuta',
                    'Nilai Kurs',
                    'Tgl Awal',
                    'Tgl Akhir'
                ]);

                $table->setRows($kursData['data']);
                $table->render();

                Log::info("kurs:update => done. inserted: {$inserted}, updated: {$updated}", $kursData['data']);
            } catch (\Exception $e) {
                DB::rollBack();
                $this->line("<error>DB ERROR: {$e->getMessage()}</>");
                Log::error("kurs:update => [ERROR] {$e->getMessage()}");
            }
        } else {
            $this->line("<error>No kurs data pulled from BKF site!</>");
            Log::warning("kurs:update => [WARNING] BKF site returned no data...");
        }
    }
}
