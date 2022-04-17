<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\Models\User;
// use App\Models\Role;
use App\Models\Loan;
use App\Models\ScheduledRepayment;
use App\Models\LoanRepayment;
use Carbon\Carbon;

class ApiController extends Controller
{

    /**
     * return true if user is admin, else false
     */
    private function isAdmin() {
        $user = Auth::user();
        if($user->role->role_name == 'admin'){
            return true;
        }
        return false;
    }

    public function requestLoan(Request $req) {
        if($this->isAdmin()) {
            return response(['message' => 'Only customers can avail loan'], 401);
        }
        $loan = null;
        $validatedData = $req->validate([
            'amount' => 'required|numeric|min:0',
            'term' => 'required|numeric|min:1' //in weeks
        ]);
        $validatedData['customer_id'] = Auth::id();
        $loan = Loan::create($validatedData);
        
        $scheduledRepayments = [];
        $repayAmount = round(($loan->amount / $loan->term), 2);
        for($i = 0; $i < $loan->term; $i++) {
            $schd_at = Carbon::now()->addDays(7*($i+1));
            array_push($scheduledRepayments, array("loan_id" => $loan->id,
                                                    "scheduled_date" => $schd_at, 
                                                    "repayment_amount" => $repayAmount
                                                    )
            );
        }
        ScheduledRepayment::insert($scheduledRepayments);
        return response(['loanId' => $loan->id], 200);
    }

    /**
     * Admin can approve a loan
     */
    public function approveLoan(Request $req, $id) {
        if($this->isAdmin()) {
            $loan = Loan::findOrFail($id);
            //if loan in pending state
            if($loan->status == 0) {
                $loan->status = 1;
                $loan->approver_id = Auth::id();
                $loan->save();
        
                return response(['message' => "Approved"], 200);        
            } 
        }     
        //if loan paid or already approved or user is customer
        return response(['message' => "Invalid Request"], 400);           
    }

    /**
     * Get all loans for logged in user
     */
    public function getLoans(Request $req) {
        
        //global scope will return loans for logged in user only
        $loans = Loan::with('repayments')
                    ->get();

        return response()->json($loans);        
    }

    /**
     * Add repayment to given loanId
     */
    public function addRepayment(Request $req, $loanId) {
        \Log::debug("Add repayment api with loanId: ".$loanId);
        $loan = Loan::where('id', $loanId)
                    ->where('status', 1) //approved
                    ->with(['repayments' => function($q) {
                        $q->where('status', 1);
                    }])->firstOrFail();

        $repayAmount = $req->post('amount');
        \Log::debug("Add repayment api with repayAmount: ".$repayAmount);
        $scheduledRepayment = $loan->repayments->first();
        if($repayAmount <= 0 || $repayAmount < $scheduledRepayment->repayment_amount) {
            return response(['message' => "Invalid Request"], 400);           
        }

        \DB::transaction(function() use($repayAmount, $loan, $scheduledRepayment) {
            $scheduledRepayment->status = 0;
            $scheduledRepayment->save();

            $lrepayment = new LoanRepayment;
            $lrepayment->loan_id = $loan->id;
            $lrepayment->repayment_date = Carbon::now();
            $lrepayment->repayment_amount = $repayAmount;
            $lrepayment->status = 0;
            $lrepayment->save();

            $noOfOpenRepayments = Loan::where('id', $loan->id)
                                        ->where('status', 1) //approved
                                        ->whereHas('repayments', function($q) {
                                            $q->where('status', 1);
                                        })->count();

            if($noOfOpenRepayments == 0) {
                $loan->status = 2;
                $loan->save();
            }            
        });
        return response(['message' => "Successful"], 200);           
    }

}
