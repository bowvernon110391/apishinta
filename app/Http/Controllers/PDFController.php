<?php

namespace App\Http\Controllers;

use App\BPPM;
use App\Printables\PdfBPPM;
use App\Printables\PdfSPP;
use App\Printables\PdfST;
use App\SPP;
use App\ST;
use App\CD;
use App\PIBK;
use App\Printables\PdfLembarHitungCD;
use App\Printables\PdfLembarHitungPIBK;
use App\Printables\PdfSPPB;
use App\SPPB;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PDFController extends ApiController
{
    /**
     * show targeted pdf
     */
    public function show(Request $r) {
        // $pdf->AddPage('P', 'A4');
        // $pdf->SetFont('Arial', '', 12);
        // $pdf->Cell(50, 25, 'Yer fond of me lobster ain\'t ye?');

        // return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');

        // try to switch etc

        try {
            $doctype = $r->get('doc');
            $id = $r->get('id');

            // both must exist, otherwise throw bard request
            if (!$doctype || !$id) {
                throw new BadRequestHttpException("Print request denied. Explain yerself!");
            }

            // build response header
            $headers = [
                'Content-Type'  => 'application/pdf',
            ];

            // if we'got a filename, then force download
            if ($r->get('filename')) {
                // fix the filename to ensure pdf extension
                $filename = $r->get('filename');

                if (strtoupper(substr($filename, -4, 4)) != '.PDF') {
                    $filename .= ".pdf";
                }

                $headers['Content-Disposition'] = "attachment; filename={$filename}";
            }

            switch ($doctype) {
                // Cetakan BPPM
                case 'bppm':
                case 'BPPM':
                    $bppm = BPPM::find($id);

                    if (!$bppm) {
                        throw new NotFoundHttpException("BPPM #{$id} was not found");
                    }

                    $pdf = new PdfBPPM($bppm);
                    $pdf->generateFirstpage();

                    return response($pdf->Output('S'), 200, $headers);
                
                // Cetakan SPP
                case 'spp':
                case 'SPP':
                    $spp = SPP::find($id);

                    if (!$spp) {
                        throw new NotFoundHttpException("SPP #{$id} was not found");
                    }

                    $pdf = new PdfSPP($spp, $r->get('kota_ttd'), $r->get('tgl_ttd'));
                    $pdf->generateFirstpage();

                    return response($pdf->Output('S'), 200, $headers);

                // Cetakan ST
                case 'st':
                case 'ST':
                    $st = ST::find($id);

                    if (!$st) {
                        throw new NotFoundHttpException("ST #{$id} was not found");
                    }

                    $pdf = new PdfST($st, $r->get('kota_ttd'), $r->get('tgl_ttd'));
                    $pdf->generateFirstpage();

                    return response($pdf->Output('S'), 200, $headers);

                // Cetakan Lembar Perhitungan CD
                case 'lembarhitungcd':
                case 'lembarcd':
                    $cd = CD::find($id);

                    if (!$cd) {
                        throw new NotFoundHttpException("CD #{$id} was not found");
                    }

                    $pdf = new PdfLembarHitungCD($cd);
                    $pdf->generateFirstPage();

                    return response($pdf->Output('S'), 200, $headers);

                case 'lembarhitungpibk':
                case 'lembarpibk':
                    $pibk = PIBK::find($id);

                    if (!$pibk) {
                        throw new NotFoundHttpException("PIBK #{$id} was not found");
                    }

                    $pdf = new PdfLembarHitungPIBK($pibk);
                    $pdf->generateFirstPage();

                    return response($pdf->Output('S'), 200, $headers);

                // Cetakan SPPB
                case 'sppb':
                case 'SPPB':
                    $sppb = SPPB::find($id);

                    if (!$sppb) {
                        throw new NotFoundHttpException("SPPB #{$id} was not found");
                    }

                    $pdf = new PdfSPPB($sppb);
                    $pdf->generateFirstPage();

                    return response($pdf->Output('S'), 200, $headers);
                default:
                    throw new BadRequestHttpException("No printing option for doctype: {$doctype}");
            }
        } catch (BadRequestHttpException $e) {
            return $this->errorBadRequest($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorInternalServer($e->getMessage());
        }
    }
}
