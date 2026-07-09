<?php

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/quotations/{quotation}/pdf', function (Quotation $quotation) {
    $quotation->load(['items.product', 'deal.lead.contact.company']);
    $pdf = Pdf::loadView('quotations.pdf', ['quotation' => $quotation]);

    return $pdf->stream("quotation-{$quotation->deal->lead->contact->company->name}-v{$quotation->version}.pdf");
})->name('quotations.pdf')->middleware('auth');

Route::get('/quotations/{quotation}/share', function (Quotation $quotation) {
    $quotation->load(['items.product', 'deal.lead.contact.company']);
    $pdf = Pdf::loadView('quotations.pdf', ['quotation' => $quotation]);

    return $pdf->stream("quotation-v{$quotation->version}.pdf");
})->name('quotations.share')->middleware('signed');
