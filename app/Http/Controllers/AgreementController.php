<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Packages\AgreementService\Services\AgreementService;
use App\Models\Agreement;

class AgreementController extends Controller
{
    public function index()
    {
        $agreements = Agreement::all();
        return view('agreement.index', ['agreements' => $agreements]);
    }

    public function store(Request $request, AgreementService $agreementService)
    {
        $data = $request->validate([
            'billing_account' => 'required|string',
            'strategy' => 'required|string',
            'multiplier' => 'required|numeric',
            'vat_rate' => 'required|numeric',
            'currency' => 'required|string',
            'language' => 'required|string',
            'rules' => 'required|json',
        ]);

        $customerId = $data['billing_account'];
        unset($data['billing_account']);
        $data['rules'] = json_decode($data['rules'], true);

        $agreementService->createNewVersion($customerId, $data);

        return back()->with('success', 'Agreement created successfully!');
    }
}