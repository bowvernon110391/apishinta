<?php

namespace App\Http\Controllers;

use App\Printables\PdfBPPM;
use App\Printables\PdfSPP;
use App\SPP;
use App\SSPCP;
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
                case 'sspcp':
                case 'SSPCP':
                case 'bppm':
                case 'BPPM':
                    $sspcp = SSPCP::find($id);

                    if (!$sspcp) {
                        throw new NotFoundHttpException("SSPCP #{$id} was not found");
                    }

                    $pdf = PdfBPPM::createFromSSPCP($sspcp);

                    return response($pdf->Output('S'), 200, $headers);
                
                case 'spp':
                case 'SPP':
                    $spp = SPP::find($id);

                    if (!$spp) {
                        throw new NotFoundHttpException("SPP #{$id} was not found");
                    }

                    $pdf = new PdfSPP($spp);
                    $pdf->generateFirstpage();

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
