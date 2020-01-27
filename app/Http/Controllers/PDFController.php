<?php

namespace App\Http\Controllers;

use App\PDFCreator;
use App\SSPCP;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
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

            switch ($doctype) {
                case 'sspcp':
                case 'SSPCP':
                    $sspcp = SSPCP::find($id);

                    if (!$sspcp) {
                        throw new NotFoundHttpException("SSPCP #{$id} was not found");
                    }

                    $pdf = new PDFCreator();
                    $pdf->addSspcp($sspcp);

                    return response($pdf->getPdf()->Output('S'), 200)
                            ->header('Content-Type', 'application/pdf');

                default:
                    throw new BadRequestHttpException("No printing option for doctype: {$doctype}");
            }
        } catch (BadRequestHttpException $e) {
            return $this->errorBadRequest($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound($e->getMessage());
        }
    }
}
