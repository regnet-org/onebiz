<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceReceiptMail;
use App\Utilities\Overrider;
use App\Transaction;
use App\Invoice;
use App\Quotation;
use App\Project;
use App\CompanySetting;
use Stripe\Stripe;
use Stripe\Charge;
use Validator;
use Auth;
use DB;

class ClientController extends Controller
{
	
	public function __construct()
    {
		date_default_timezone_set(get_company_option('timezone',get_option('timezone','Asia/Dhaka')));	
	}

	/** Method Use for selecting company by Client **/
    public function select_business(Request $request){
    	if($request->isMethod('post')){
            $company_id = $request->company_id;
            session(['company_id' => $company_id]);
            return back();
    	}else{
    		return view('backend.client_panel.modal.select_business');
    	}
		
	}

    public function invoices($status = ''){
	  $data = array();

	  $data['company_currency'] = array();
	  $data['currency_position'] = array();
    
      foreach(Auth::user()->client as $client){
          $data['company_currency'][$client->company_id] = get_company_field($client->company_id,'base_currency');
          $data['currency_position'][$client->company_id] = get_company_field($client->company_id,'currency_position');
      }

	  $client_id = Auth::user()->client->pluck('id');

	  if($status != ''){
		  $data['invoices'] = Invoice::whereIn('client_id',$client_id)
									 ->where('status',$status)
									 ->get();
	  }else{
		  $data['invoices'] = Invoice::whereIn('client_id',$client_id)->get();
	  }
	  return view('backend.client_panel.invoices',$data);						   

    }
	
	public function view_invoice($id){
		$invoice = Invoice::whereRaw('md5(id) = "' . $id . '"')
						  ->first();
		if(! $invoice){
			return back()->with('error', _lang('Sorry, Invoice not found !'));
		}				  
		$transactions = Transaction::where('invoice_id',$id)->get();
		
		$company = CompanySetting::where('company_id',$invoice->company_id)->get();
		
		$template = $invoice->template;
		if($invoice->template == ""){
			$template = "modern";
		}
		return view("backend.client_panel.invoice_template.$template",compact('invoice','transactions','company'));	
	}

	public function quotations(){
	  $data = array();
	  $data['company_currency'] = array();
	  $data['currency_position'] = array();
    
      foreach(Auth::user()->client as $client){
          $data['company_currency'][$client->company_id] = get_company_field($client->company_id,'base_currency');
          $data['currency_position'][$client->company_id] = get_company_field($client->company_id,'currency_position');
      }
	  $client_id = Auth::user()->client->pluck('id');

	  $data['quotations'] = Quotation::whereIn('related_id',$client_id)
	  								 ->where('related_to','contacts')
	  								 ->get();

	  return view('backend.client_panel.quotations',$data);						   

    }
	
	public function view_quotation($id){
		$quotation = Quotation::whereRaw('md5(id) = "' . $id . '"')
		                      ->first();
	    $company = CompanySetting::where('company_id',$quotation->company_id)->get();
		
		$template = $quotation->template;
		if($quotation->template == ""){
			$template = "general";
		}
		return view("backend.client_panel.quotation-template.$template",compact('quotation','company'));	
	}
	
	public function transactions(){
		$data = array();
		$data['company_currency'] = array();
		$data['currency_position'] = array();
	    
	    foreach(Auth::user()->client as $client){
	          $data['company_currency'][$client->company_id] = get_company_field($client->company_id,'base_currency');
	          $data['currency_position'][$client->company_id] = get_company_field($client->company_id,'currency_position');
	    }
		$client_id = Auth::user()->client->pluck('id');
		$data['transactions'] = Transaction::whereIn('payer_payee_id',$client_id)->get();

	    return view('backend.client_panel.transactions',$data);
	}
	
	public function view_transaction(Request $request, $id){

		$data = array();
		$data['company_currency'] = array();
		$data['currency_position'] = array();
	    
	    foreach(Auth::user()->client as $client){
	          $data['company_currency'][$client->company_id] = get_company_field($client->company_id,'base_currency');
	          $data['currency_position'][$client->company_id] = get_company_field($client->company_id,'currency_position');
	    }

		$client_id = Auth::user()->client->pluck('id');
		$data['transaction'] = Transaction::where('id',$id)
							      ->whereIn('payer_payee_id',$client_id)->first();
	    if($request->ajax()){
		    return view('backend.client_panel.view_transaction',$data);
		}

	}


	public function projects(){
		$client_ids = Auth::user()->client->pluck('id');
		$data = array();

        $data['projects'] = Project::whereIn('client_id',$client_ids)
								  ->orderBy('id','desc')
								  ->get();
		return view('backend.client_panel.projects',$data);						  
	}


	public function view_project($id){
		$client_ids = Auth::user()->client->pluck('id');

        $data = array();

        $data['project'] = Project::where('projects.id', $id)
                                   ->whereIn('client_id', $client_ids)
                                   ->first();

        if(! $data['project']){
			abort(404);
        }

        //get Summary data
        $data['hour_completed'] = \App\TimeSheet::where('project_id',$id)
                                                ->selectRaw("SUM( TIMESTAMPDIFF(SECOND, start_time, end_time) ) as total_seconds")
                                                ->first();


        $data['invoices'] = \App\Invoice::where('related_to','projects')
                                        ->where('related_id', $id)
                                        ->get();

        $data['expenses'] = \App\Transaction::where("project_id",$id)
                                            ->orderBy("transactions.id","desc")
                                            ->get();

        $data['tasks'] = \App\Task::where('project_id',$id)
                                  ->get();           

        $data['timesheets'] = \App\TimeSheet::where('project_id',$id)
                                            ->orderBy('id','desc')
                                            ->get();                     

        $data['project_milestones']  = \App\ProjectMilestone::where('project_id',$id)
                                                            ->orderBy('id','desc')
                                                            ->get();


        $data['projectfiles'] = \App\ProjectFile::where('related_id', $id)
                                                ->where('related_to', 'projects')
                                                ->orderBy('id','desc')
                                                ->get();

        $data['notes'] = \App\Note::where('related_id', $id)
                                  ->where('related_to', 'projects')
                                  ->orderBy('id','desc')
                                  ->get();                    
        
        return view('backend.client_panel.view_project', $data);
	}


    /**
     * Store File to Project.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload_file(Request $request)
    { 

        $max_size = get_option('file_manager_max_upload_size',2) * 1024;
        $supported_file_types = get_option('file_manager_file_type_supported','png,jpg,jpeg');
         
        $validator = Validator::make($request->all(), [
            'related_id' => 'required',
            'file' => "required|file|max:$max_size|mimes:$supported_file_types",
        ]);

        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return back()->withErrors($validator)
                             ->withInput();
            }            
        }
    
        $file_path = '';
        if($request->hasfile('file'))
        {
            $file = $request->file('file');
            $file_path = time().$file->getClientOriginalName();
            $file->move(public_path()."/uploads/project_files/", $file_path);
        }

        $projectfile = new \App\ProjectFile();
        $projectfile->related_to = 'projects';
        $projectfile->related_id = $request->input('related_id');
        $projectfile->file = $file_path;
        $projectfile->user_id = Auth::id();
        $projectfile->company_id = company_id();

        $projectfile->save();

        create_log('projects', $projectfile->related_id, _lang('Uploaded File'));

        //Prefix output
        $projectfile->file = '<a href="'. url('projects/download_file/'.$projectfile->file) .'">'.$projectfile->file .'</a>';
        $projectfile->user_id = '<a href="'. action('StaffController@show', $projectfile->user->id) .'" data-title="'. _lang('View Staf Information') .'"class="ajax-modal-2">'. $projectfile->user->name .'</a>';
        $projectfile->remove = '<a class="ajax-get-remove" href="'. url('projects/delete_file/'.$projectfile->id) .'">'. _lang('Remove') .'</a>';

        if(! $request->ajax()){
           return back()->with('success', _lang('File Uploaded Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'store','message'=>_lang('File Uploaded Sucessfully'),'data'=>$projectfile, 'table' => '#files_table']);
        }
        
   }

   /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_file(Request $request, $id)
    {

        $projectfile = \App\ProjectFile::where('id',$id)
                                       ->where('user_id',Auth::id())
                                       ->first();
        if(!$projectfile){
            if(! $request->ajax()){
               return back()->with('error',_lang('Sorry only admin or creator can remove this file !'));
            }else{
               return response()->json(['result'=>'error','message'=>_lang('Sorry only admin or creator can remove this file !')]);
            }

        }                              
        unlink(public_path('uploads/project_files/'.$projectfile->file));
        $projectfile->delete();

        create_log('projects', $id, _lang('File Removed'));
        
        if(! $request->ajax()){
           return back()->with('success',_lang('Removed Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'delete','message'=>_lang('Removed Sucessfully'),'id'=>$id, 'table' => '#files_table']);
        }
        
    }

    public function download_file(Request $request, $file){
        $file = 'public/uploads/project_files/'.$file;
        return response()->download($file);
    }

    /**
     * Store note.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create_note(Request $request)
    {    
        $validator = Validator::make($request->all(), [
            'related_id' => 'required',
            'note' => 'required',
        ]);

        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('notes.create')
                                 ->withErrors($validator)
                                 ->withInput();
            }            
        }
      
        $note = new \App\Note();
        $note->related_to ='projects';
        $note->related_id = $request->input('related_id');
        $note->note = $request->input('note');
        $note->user_id = Auth::id();
        $note->company_id = company_id();

        $note->save();

        create_log('projects', $note->related_id, _lang('Added Note'));

        //Prefix Output
        $note->created = '<small>'.$note->user->name.'('.$note->created_at.')<br>'.$note->note.'</small>';
        $note->action = '<a href="'. url('projects/delete_note/'.$note->id) .'" class="ajax-get-remove"><i class="far fa-trash-alt text-danger"></i></a>';

        if(! $request->ajax()){
           return back()->with('success', _lang('Saved Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Sucessfully'), 'data'=>$note, 'table' => '#notes_table']);
        }
        
   }

   /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_note(Request $request, $id)
    {
        $note = \App\Note::where('id',$id)
                         ->where('user_id',Auth::id())
                         ->first();
        if(!$note){
            if(! $request->ajax()){
               return back()->with('error',_lang('Sorry only admin or creator can remove this file !'));
            }else{
               return response()->json(['result'=>'error', 'message'=> _lang('Sorry only admin or creator can remove this file !')]);
            }
        }  
                                    
        $note->delete();
        create_log('projects', $id, _lang('Removed Note'));
        

        if(! $request->ajax()){
           return back()->with('success',_lang('Removed Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'delete','message'=>_lang('Removed Sucessfully'),'id'=>$id, 'table' => '#notes_table']);
        }
        
    }


	/** Stripe Payment Gateway **/
	public function make_payment($invoice_id){
		@ini_set('max_execution_time', 0);
		@set_time_limit(0);

		$client_id = Auth::user()->client->pluck('id');
		
		$invoice = Invoice::where('id',$invoice_id)
						  ->whereIn('client_id',$client_id)->first();
	    
		
		Stripe::setApiKey(get_company_field($invoice->company_id, 'stripe_secret_key'));

		$base_currency = get_company_field($invoice->company_id, 'base_currency');
		$stripe_currency = get_company_field($invoice->company_id, 'stripe_currency');
 
        $token = request('stripeToken');
		
        $charge = Charge::create([
            'amount' => round(convert_currency($base_currency, $stripe_currency, (($invoice->grand_total-$invoice->paid) * 100))),
            'currency' => $stripe_currency,
            'description' => _lang('Invoice Payment'),
            'source' => $token,
        ]);
		
		//dd($charge);
		$company_id = $invoice->company_id;
		
		if(get_company_field($company_id, 'default_account') != '' && get_company_field($company_id, 'default_chart_id') != ''){
			$transaction = new Transaction();
			$transaction->trans_date = date('Y-m-d');
			$transaction->account_id = get_company_field($company_id, 'default_account');
			$transaction->chart_id = get_company_field($company_id, 'default_chart_id');
			$transaction->type = 'income';
			$transaction->dr_cr = 'cr';
			//$transaction->amount = ($charge['amount'] / 100);
			$transaction->amount = convert_currency($stripe_currency, $transaction->account->account_currency, ($charge['amount'] / 100));
		    $transaction->base_amount = convert_currency($stripe_currency, $base_currency, ($charge['amount'] / 100));
			$transaction->payer_payee_id = $invoice->client_id;
			$transaction->payment_method_id = payment_method('Stripe',$invoice->company_id);
			$transaction->invoice_id = $invoice->id;
			//$transaction->reference = $request->input('reference');
			//$transaction->note = $request->input('note');
			$transaction->company_id = $company_id;
			
			$transaction->save();
		}
	
		
		//Update Invoice Table					  
		//$invoice->paid = $invoice->paid + ($charge['amount']/100);				
		$invoice->paid = $invoice->paid + $transaction->base_amount;				
        $invoice->status = 'Paid';
		$invoice->save();
		
		//Send Invoice Payment Confrimation to Client
	    Overrider::load("Settings");
		$mail  = new \stdClass();
		$mail->subject = _lang('Invoice Payment');
		$mail->invoice = $invoice;
		$mail->transaction = $transaction;
		$mail->method = "Stripe";
		$mail->currency = currency();
		
		try{
			Mail::to($invoice->client->contact_email)->send(new InvoiceReceiptMail($mail));
		}catch (\Exception $e) {
			//Nothing
		}

		return redirect('client/view_invoice/'.encrypt($invoice->id))->with('success', _lang('Thank You, Your payment was made sucessfully.'));
	    
	}
	
	/* PayPal Payment Gateway */
	public function paypal($action, $invoice_id){
		if($action == "return"){
			return redirect('client/view_invoice/'.encrypt($invoice_id))->with('success', _lang('Thank You, Your payment was made sucessfully.'));
		}else if($action == "cancel"){
			return redirect('client/view_invoice/'.encrypt($invoice_id))->with('error', _lang('Payment Canceled !'));
		}
	}
	

	public function paypal_ipn(Request $request)
	{
		$invoice_number = $request->item_number;
		$id = $request->custom;
		$amount = $request->mc_gross;
		
		$base_amount = convert_currency( get_company_field($invoice->company_id, 'paypal_currency'), get_company_field($invoice->company_id, 'base_currency'), $amount );
		
		$invoice = Invoice::where('invoice_number',$invoice_number)
		                  ->where('id',$id)->first();

		if( $base_amount >= ($invoice->grand_total - $invoice->paid)){
			if(get_company_field($invoice->company_id, 'default_account') != '' && get_company_field($invoice->company_id, 'default_chart_id') != ''){
			
				$transaction = new Transaction();
				$transaction->trans_date = date('Y-m-d');
				$transaction->account_id = get_company_field($invoice->company_id, 'default_account');
				$transaction->chart_id = get_company_field($invoice->company_id, 'default_chart_id');
				$transaction->type = 'income';
				$transaction->dr_cr = 'cr';
				//$transaction->amount = $amount;
				$transaction->amount = convert_currency( get_company_field($invoice->company_id, 'paypal_currency'), $transaction->account->account_currency, $amount );
		        $transaction->base_amount = $base_amount;
				$transaction->payer_payee_id = $invoice->client_id;
				$transaction->payment_method_id = payment_method('PayPal',$invoice->company_id);
				$transaction->invoice_id = $invoice->id;
				$transaction->company_id = $invoice->company_id;
				
				$transaction->save();
			}
	
		
			//Update Invoice Table					  
			$invoice->paid = ($invoice->paid + $transaction->base_amount);				
			$invoice->status = 'Paid';
			$invoice->save();	
			
			//Send Invoice Payment Confrimation to Client
			@ini_set('max_execution_time', 0);
			@set_time_limit(0);
			Overrider::load("Settings");
			$mail  = new \stdClass();
			$mail->subject = _lang('Invoice Payment');
			$mail->invoice = $invoice;
			$mail->transaction = $transaction;
			$mail->method = "PayPal";
			$mail->currency = currency( get_company_field( $invoice->company_id, 'base_currency' ) );
			
			try{
				Mail::to($invoice->client->contact_email)->send(new InvoiceReceiptMail($mail));
			}catch (\Exception $e) {
				//Nothing
			}
			
        }
    }

	
}
