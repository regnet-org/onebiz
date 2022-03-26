<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTemplate;
use App\Stock;
use App\Transaction;
use App\Project;
use App\CompanySetting;
use App\Contact;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralMail;
use App\Mail\InvoiceReceiptMail;
use App\Utilities\Overrider;
use Notification;
use App\Notifications\InvoiceCreated;
use App\Notifications\InvoiceUpdated;
use Carbon\Carbon;
use DataTables;
use DB;
use PDF;
use App\FileManager;

use App\Mail\PremiumMembershipMail;
use App\EmailTemplate;


class InvoiceController extends Controller
{

	 /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    	date_default_timezone_set(get_company_option('timezone',get_option('timezone','Asia/Dhaka')));	

        $this->middleware(function ($request, $next) {
            if( has_membership_system() == 'enabled' ){
                if( ! has_feature( 'invoice_limit' ) ){
                    return redirect('membership/extend')->with('message',_lang('Your Current package not support this feature. You can upgrade your package !'));
                }

                // If request is create/store
                $route_name = \Request::route()->getName();
                if( $route_name == 'invoices.store'){
                   if( ! has_feature_limit( 'invoice_limit' ) ){
                      if( ! $request->ajax()){
                          return redirect('membership/extend')->with('message', _lang('Your have already reached your usages limit. You can upgrade your package !'));
                      }else{
                          return response()->json(['result'=>'error','message'=> _lang('Your have already reached your usages limit. You can upgrade your package !') ]);
                      }
                   }
                }
            }

            return $next($request);
        });
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.accounting.invoice.list');
    }
	
	
	public function get_table_data($export=false){
		$currency = currency();
		$company_id =company_id(); 

		$projects = DB::table('projects')
	                  ->select('id','name as contact_name',DB::raw('"projects" as type'))
	                  ->where('company_id', $company_id);

		$all_contacts = DB::table('contacts')
		                  ->select('id','contact_name',DB::raw('"contacts" as type'))
		                  ->where('company_id', $company_id)
		                  ->union($projects);

        $invoices = Invoice::joinSub($all_contacts, 'all_contacts', function ($join) {
						            $join->on('invoices.related_id', '=', 'all_contacts.id')
						                 ->on('invoices.related_to', '=', 'all_contacts.type');
						        })
		                       ->select("invoices.*", "all_contacts.contact_name", "all_contacts.id as contact_id")
							   ->where('invoices.company_id', $company_id);
	                           // ->orderBy('invoices.id', 'desc');                  
	    if($export) {
	    	if(request()->date_type_select==1) {
	    		if(request()->date_month !== "" && request()->date_month != -1 && request()->date_month !== NULL) {
	    			$d = Carbon::createFromFormat('Y-m-d', request()->date_year."-".(request()->date_month+1)."-01");
	    			$invoices->where('invoice_date', '>=', $d->firstOfMonth()->format('Y-m-d'));
	    			$invoices->where('invoice_date', '<=', $d->endOfMonth()->format('Y-m-d'));
	    		} else {
	    		    $d = Carbon::createFromFormat('Y-m-d', request()->date_year."-01-01");
	    			$invoices->where('invoice_date', '>=', $d->startOfYear()->format('Y-m-d'));
	    			$invoices->where('invoice_date', '<=', $d->endOfYear()->format('Y-m-d'));
	    		}
	    		
	    	} else if(request()->date_type_select == 2) {
    			$invoices->where('invoice_date', '>=', request()->start_date);
    			$invoices->where('invoice_date', '<=', request()->end_date);
	    	} else if(request()->date_type_select == 3) {
    			$invoices->where('invoice_date', '>=', Carbon::now()->subDays(7)->format('Y-m-d'));
	    	} else if(request()->date_type_select == 4) {
    			$invoices->where('invoice_date', '>=', Carbon::now()->subDays(30)->format('Y-m-d'));
	    	}
	    	
	    	return $invoices->get();
	    }
						   

		return Datatables::eloquent($invoices)
						->addColumn('contact_name', function ($invoice) {
							if($invoice->related_to == 'contacts'){
								return '<a href="'.action('ContactController@show', $invoice->related_id).'">'.$invoice->contact_name.' <span class="text-muted small">('._lang('Customer').')</span></a>';
							}
							return '<a href="'.action('ProjectController@show', $invoice->related_id).'">'.$invoice->contact_name.' <span class="text-muted small">('._lang('Project').')</span></a>';
						})
						->filterColumn('contact_name', function($query, $keyword) {
		                    $sql = "all_contacts.contact_name  like ?";
		                    $query->whereRaw($sql, ["%{$keyword}%"]);
		                })
						->editColumn('due_date', function ($invoice) {
							$date_format = get_company_option('date_format','Y-m-d');
							return date($date_format, strtotime($invoice->due_date));
						})
						->editColumn('grand_total', function ($invoice) use ($currency){		
						    $acc_currency = currency($invoice->client->currency);
							if($acc_currency != $currency){
								return "<span class='float-right'>".decimalPlace($invoice->grand_total, $currency)."</span><br>
										<span class='float-right'><b>".decimalPlace($invoice->converted_total, $acc_currency)."</b></span>";
							}else{
								return "<span class='float-right'>".decimalPlace($invoice->grand_total, $currency)."</span>";
							}
						})
						->editColumn('status', function ($invoice) {
							return invoice_status($invoice->status);
						})
						->addColumn('action', function ($invoice) {

							$ddlinks = '';
							$addaction = '';
							if($invoice->status == 'Unpaid')
							{
								$addaction .= '<a href="'.  route('invoices.mark_as_cancelled',$invoice->id) .'" data-title="'. _lang('Mark As Cancelled') .'" data-fullscreen="true" class="dropdown-item"><i class="fas fa-times"></i> '. _lang('Mark As Cancelled') .'</a>';
							}
							if($invoice->status != 'Canceled' && $invoice->status != 'Storno')
							{
								$addaction .= '<a href="'.  route('invoices.storno',$invoice->id) .'" data-title="'. _lang('Storno') .'" data-fullscreen="true" class="dropdown-item"><i class="fas fa-undo"></i> '. _lang('Storno') .'</a>';
							}
								$ddlinks .= '<div class="dropdown">'
										.'<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">'._lang('Action')
										.'&nbsp;<i class="fas fa-angle-down"></i></button>'
										.'<div class="dropdown-menu">';
								$ddlinks .= '<a class="dropdown-item" href="'. action('InvoiceController@edit', $invoice->id) .'"><i class="fas fa-edit"></i> '._lang('Edit') .'</a>';
								$ddlinks .= '<a class="dropdown-item" href="'. action('InvoiceController@show', $invoice->id) .'" data-title="'._lang('View Invoice') .'" data-fullscreen="true"><i class="fas fa-eye"></i> '._lang('View') .'</a>';

							if($invoice->status != 'Storno')
							{
								$ddlinks .= '<a href="'. url('invoices/create_payment/'.$invoice->id) .'" data-title="'. _lang('Make Payment') .'" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> '._lang('Make Payment') .'</a>'
											.'<a href="'. url('invoices/view_payment/'.$invoice->id) .'" data-title="'. _lang('View Payment') .'" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> '. _lang('View Payment') .'</a>';
							}				
								$ddlinks .= $addaction;
								$ddlinks .= '<form action="'. action('InvoiceController@destroy', $invoice['id']) .'" method="post">'								
													.csrf_field()
													.'<input name="_method" type="hidden" value="DELETE">'
													.'<button class="button-link btn-remove" type="submit"><i class="fas fa-recycle"></i> '._lang('Delete') .'</button>'
												.'</form>';	
								$ddlinks .= '</div>'
										.'</div>';
							return $ddlinks;			

						})
						->setRowId(function ($invoice) {
							return "row_".$invoice->id;
						})
						->rawColumns(['grand_total','status','action','contact_name'])
						->make(true);							    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
		if( ! $request->ajax()){
		   return view('backend.accounting.invoice.create');
		}else{
           return view('backend.accounting.invoice.modal.create');
		}
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {	
		$validator = Validator::make($request->all(), [
			'invoice_number' => 'required|max:191',
            'related_to' => 'required',
            'client_id' => 'required_if:related_to,contacts',
            'project_id' => 'required_if:related_to,projects',
            'invoice_date' => 'required',
            'due_date' => 'required',
            'product_id' => 'required',
            'template' => 'required',
		],[
		   'product_id.required' => _lang('You must select at least one product or service')
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect('invoices/create')
							->withErrors($validator)
							->withInput();
			}			
		}
		
		DB::beginTransaction();
		
	    $company_id = company_id();
		
        $invoice = new Invoice();
	    $invoice->invoice_number = $request->input('invoice_number');
        $invoice->invoice_date = $request->input('invoice_date');
        $invoice->due_date = $request->input('due_date');
        $invoice->grand_total = $request->input('product_total');
        $invoice->tax_total = $request->input('tax_total');
        $invoice->paid = 0;
        $invoice->status = 'Unpaid';
        $invoice->template = $request->input('template');
        $invoice->note = $request->input('note');
        $invoice->invoice_created_name = $request->input('invoice_created_name');
        $invoice->invoice_created_cnp = $request->input('invoice_created_cnp');
        $invoice->note = $request->input('note');
		$invoice->related_to = $request->input('related_to');

        if($invoice->related_to == 'contacts'){
			$invoice->related_id = $request->input('client_id');
			$invoice->client_id = $request->input('client_id');
			$invoice->converted_total = convert_currency(base_currency(), $invoice->client->currency, $invoice->grand_total);	
        }else if($invoice->related_to == 'projects'){
			$invoice->related_id = $request->input('project_id');
			$invoice->client_id = Project::find($invoice->related_id)->client_id;	
			$invoice->converted_total = convert_currency(base_currency(), $invoice->project->client->currency, $invoice->grand_total);
        }

        $invoice->company_id = $company_id;

        $invoice->save();



        //Save Invoice Item
        for($i=0; $i<count($request->product_id); $i++ ){
			$invoiceItem = new InvoiceItem();
			$invoiceItem->invoice_id = $invoice->id;
			$invoiceItem->item_id = $request->product_id[$i];
			$invoiceItem->quantity = $request->quantity[$i];
			$invoiceItem->unit_cost = $request->unit_cost[$i];
			$invoiceItem->discount = $request->discount[$i];
			$invoiceItem->tax_method = $request->tax_method[$i];
			$invoiceItem->tax_id = $request->tax_id[$i];
			$invoiceItem->tax_amount = $request->tax_amount[$i];
			$invoiceItem->sub_total = $request->sub_total[$i];
			$invoiceItem->company_id = $company_id;
			$invoiceItem->save();

			//Update Stock if Order Status is received
			if( has_feature('inventory_module') ){
				if($request->input('order_status') != 'Canceled'){
					$stock = Stock::where("product_id",$invoiceItem->item_id)->where("company_id",$company_id)->first();
					if(!empty($stock)){
						if($stock->quantity > 0)
						{
							$stock->quantity =  $stock->quantity - $invoiceItem->quantity;
						}
						else
						{
							$stock->quantity = 0;
						}
						$stock->company_id =  $company_id;
						$stock->save();
					}
				}
			}
        }
        
        //Increment Invoice Starting number
        increment_invoice_number();
		
		//Update Package limit
		update_package_limit('invoice_limit');

/*************** save invoice to pdf ********************/

		$invid = $invoice->id;
		$data['invoice'] = $invoice;
		$data['transactions'] = Transaction::where("invoice_id",$invid)
								   ->where("company_id",company_id())->get();
		$data['company'] = CompanySetting::where('company_id',$data['invoice']->company_id)->get();

    	
        $template = $data['invoice']->template;
		if($template == ""){
			$template = "modern";
		}
		if(! file_exists(resource_path("views/backend/accounting/invoice/template/$template.blade.php"))){
        	$template = 'modern';                             
        }
		$pdf = PDF::loadView("backend.client_panel.invoice_template.pdf.$template", $data);
        $pdfpath = public_path("/uploads/file_manager");
        $pdfdate = $invoice->invoice_date;
        // $pdfdate = date('Y-m-d');
		$pdffileName = 'Invoice_'.$pdfdate.'_'.$invoice->invoice_number.'_'.$invoice->id.'.pdf';
		$pdf->save($pdfpath . '/' . $pdffileName);
		$this->filemanager_store_invoice($pdffileName, $pdfpath, $pdfdate);
/***********************************/


		if($invoice->client->user->id != null){
           Notification::send($invoice->client->user, new InvoiceCreated($invoice));
        }
		
		DB::commit();
        
		if(! $request->ajax()){
           return redirect('invoices/'.$invoice->id)->with('success', _lang('Invoice Created Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Invoice Created Sucessfully'),'data'=>$invoice]);
		}
        
   }
	

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $invoice = Invoice::where("id",$id)->where("company_id",company_id())->first();
		
		if(! $invoice){
			return back()->with('error', _lang('Sorry, Invoice not found !'));
		}
		
		$transactions = Transaction::where("invoice_id",$id)
								   ->where("company_id",company_id())->get();
		if(! $request->ajax()){
			$template = $invoice->template;

			if($invoice->template == ""){
				$template = "modern";
			}
            
            if(! file_exists(resource_path("views/backend/accounting/invoice/template/$template.blade.php"))){
            	$template = InvoiceTemplate::where('id',5)
            	                            ->where('company_id',company_id())
            	                            ->first();
            	                            
                return view("backend.accounting.invoice.template.custom",compact('invoice','transactions','template', 'id'));
            }

		    return view("backend.accounting.invoice.template.$template",compact('invoice','transactions','id'));
		}   
    }
	
	/**
     * Generate PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	public function download_pdf(Request $request, $id)
    {
		@ini_set('max_execution_time', 0);
	    @set_time_limit(0);
		
		$id = decrypt($id);
	
		$invoice = Invoice::where("id",$id)->where("company_id",company_id())->first();
		$data['invoice'] = $invoice;
		$data['transactions'] = Transaction::where("invoice_id",$id)
								   ->where("company_id",company_id())->get();
		$data['company'] = CompanySetting::where('company_id',$data['invoice']->company_id)->get();
		
        $template = $data['invoice']->template;
		if($template == ""){
			$template = "modern";
		}

		if(! file_exists(resource_path("views/backend/accounting/invoice/template/$template.blade.php"))){
        	//$data['template'] = InvoiceTemplate::where('id',5)
        	                                   //->where('company_id',company_id())
        	                                   //->first();
        	$template = 'modern';                             
        }
				
		$pdf = PDF::loadView("backend.client_panel.invoice_template.pdf.$template", $data);
		$pdf->setWarnings(false);
		//return $pdf->stream();
		return $pdf->download("invoice_{$invoice->invoice_number}.pdf");

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $invoice = Invoice::where("id",$id)->where("company_id",company_id())->first();
		if(! $request->ajax()){
		   return view('backend.accounting.invoice.edit',compact('invoice','id'));
		}else{
           return view('backend.accounting.invoice.modal.edit',compact('invoice','id'));
		}  
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$validator = Validator::make($request->all(), [
			'invoice_number' => 'required|max:191',
            'related_to' => 'required',
            'client_id' => 'required_if:related_to,contacts',
            'project_id' => 'required_if:related_to,projects',
            'invoice_date' => 'required',
            'due_date' => 'required',
            'product_id' => 'required',
			'template' => 'required',
		],[
		   'product_id.required' => _lang('You must select at least one product or service')
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect()->route('invoices.edit', $id)
							->withErrors($validator)
							->withInput();
			}			
		}
	
	    DB::beginTransaction();
        $company_id = company_id();
		
        $invoice = Invoice::where("id",$id)->where("company_id",$company_id)->first();
        $pdfdate_old = $invoice->invoice_date;
		$previous_amount = $invoice->grand_total;
		$invoice->invoice_number = $request->input('invoice_number');
        $invoice->invoice_date = $request->input('invoice_date');
        $invoice->due_date = $request->input('due_date');
        $invoice->grand_total = $request->input('product_total');
        $invoice->tax_total = $request->input('tax_total');
		//$invoice->status = $request->input('status');
		$invoice->template = $request->input('template');
		$invoice->invoice_created_name = $request->input('invoice_created_name');
        $invoice->invoice_created_cnp = $request->input('invoice_created_cnp');
        $invoice->note = $request->input('note');
		$invoice->related_to = $request->input('related_to');

        if($invoice->related_to == 'contacts'){
			$invoice->related_id = $request->input('client_id');
			$invoice->client_id = $invoice->related_id;
			if($previous_amount != $invoice->grand_total){
			    $invoice->converted_total = convert_currency(base_currency(), $invoice->client->currency, $invoice->grand_total);
			}
			$invoice->client_id = $invoice->related_id;
        }else if($invoice->related_to == 'projects'){
			$invoice->related_id = $request->input('project_id');
			$invoice->client_id = Project::find($invoice->related_id)->client_id;
			if($previous_amount != $invoice->grand_total){
			    $invoice->converted_total = convert_currency(base_currency(), $invoice->project->client->currency, $invoice->grand_total);
			}		
        }

        $invoice->company_id = $company_id;
        $invoice->save();
        
        //Update Invoice item
		$invoiceItem = InvoiceItem::where("invoice_id",$id);
        $invoiceItem->delete();

		for($i=0; $i<count($request->product_id); $i++ ){
			$invoiceItem = new InvoiceItem();
			$invoiceItem->invoice_id = $invoice->id;
			$invoiceItem->item_id = $request->product_id[$i];
			$invoiceItem->quantity = $request->quantity[$i];
			$invoiceItem->unit_cost = $request->unit_cost[$i];
			$invoiceItem->discount = $request->discount[$i];
			$invoiceItem->tax_method = $request->tax_method[$i];
			$invoiceItem->tax_id = $request->tax_id[$i];
			$invoiceItem->tax_amount = $request->tax_amount[$i];
			$invoiceItem->sub_total = $request->sub_total[$i];
			$invoiceItem->company_id = $company_id;
			$invoiceItem->save();
		}

		if($invoice->client->user->id != null){
           Notification::send($invoice->client->user, new InvoiceUpdated($invoice));
        }

/*************** save invoice to pdf ********************/
		// $invid = $invoice->id;
		$data['invoice'] = $invoice;
		$data['transactions'] = Transaction::where("invoice_id",$id)->where("company_id",company_id())->get();
		$data['company'] = CompanySetting::where('company_id',$data['invoice']->company_id)->get();

        $template = $data['invoice']->template;
		if($template == ""){
			$template = "modern";
		}
		if(! file_exists(resource_path("views/backend/accounting/invoice/template/$template.blade.php"))){
        	$template = 'modern';                             
        }
		$pdf = PDF::loadView("backend.client_panel.invoice_template.pdf.$template", $data);
        $pdfpath = public_path("/uploads/file_manager");
        $pdfdate = $invoice->invoice_date;
        // $pdfdate = date('Y-m-d');
        $pdffileName_oldstyle = 'Invoice_'.$pdfdate_old.'_'.$invoice->invoice_number.'.pdf';
        $pdffileName_old = 'Invoice_'.$pdfdate_old.'_'.$invoice->invoice_number.'_'.$id.'.pdf';
		$pdffileName = 'Invoice_'.$pdfdate.'_'.$invoice->invoice_number.'_'.$id.'.pdf';
		$remove_file = $this->filemanager_store_invoice($pdffileName, $pdfpath, $pdfdate_old, $pdffileName_old, $pdffileName_oldstyle, $pdfdate);
		// echo 1;return;
		if($remove_file=="old")
        	\File::delete($pdfpath . '/' . $pdffileName_old);
        elseif($remove_file=="oldstyle")
        	\File::delete($pdfpath . '/' . $pdffileName_oldstyle);
		$pdf->save($pdfpath . '/' . $pdffileName);
/***********************************/
	


		
		DB::commit();
		
		if(! $request->ajax()){
           return redirect('invoices/'.$invoice->id)->with('success', _lang('Invoice updated sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Invoice updated sucessfully'),'data'=>$invoice]);
		}
	    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		DB::beginTransaction();
		
        $invoice = Invoice::where("id",$id)->where("company_id",company_id());
        $inv = $invoice->first();
        $invoice->delete();
        
        $pdf_filename = 'Invoice_'.$inv->invoice_date.'_'.$inv->invoice_number.'_'.$inv->id.'.pdf';
        \File::delete(public_path("/uploads/file_manager") . '/'.$pdf_filename);
        FileManager::where(["company_id"=>company_id(), 'file'=>$pdf_filename])->delete();

        $invoiceItem = InvoiceItem::where("invoice_id",$id);
        $invoiceItem->delete();
        
		DB::commit();
		
        return redirect('invoices')->with('success',_lang('Invoice deleted sucessfully'));
    }
	
	public function create_payment(Request $request, $id)
    {
		$invoice = Invoice::where("id",$id)->where("company_id",company_id())->first();
		
		if($request->ajax()){
		   return view('backend.accounting.invoice.modal.create_payment',compact('invoice','id'));
		} 
	}
	
	public function store_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'invoice_id' => 'required',
			'account_id' => 'required',
			'chart_id' => 'required',
			'amount' => 'required|numeric',
			'payment_method_id' => 'required',
			'reference' => 'nullable|max:50',
			'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect('income/create')
							->withErrors($validator)
							->withInput();
			}			
		}

		$attachment = "";
        if($request->hasfile('attachment'))
		{
		  $file = $request->file('attachment');
		  $attachment = time().$file->getClientOriginalName();
		  $file->move(public_path()."/uploads/transactions/", $attachment);
		}
			
		DB::beginTransaction();
		
        $company_id = company_id();
		
        $transaction= new Transaction();
	    $transaction->trans_date = date('Y-m-d');
		$transaction->account_id = $request->input('account_id');
		$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'income';
		$transaction->dr_cr = 'cr';
		$transaction->amount = $request->input('amount');
		$transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
		$transaction->payer_payee_id = $request->input('client_id');
		$transaction->payment_method_id = $request->input('payment_method_id');
		$transaction->invoice_id = $request->input('invoice_id');
		$transaction->reference = $request->input('reference');
		$transaction->note = $request->input('note');
		$transaction->attachment = $attachment;
		$transaction->company_id = $company_id;
		
        $transaction->save();
		
		//Update Invoice Table
		$invoice = Invoice::where("id",$transaction->invoice_id)
						  ->where("company_id",$company_id)->first();
						  
		$invoice->paid = $invoice->paid + $transaction->base_amount;				
        if(round($invoice->paid,2) >= $invoice->grand_total){
			$invoice->status = 'Paid';
		}else if(round($invoice->paid,2) > 0 && (round($invoice->paid,2) < $invoice->grand_total)){
			$invoice->status = 'Partially_Paid';
		}
		$invoice->save();
		
		//Send Invoice Payment Confrimation to Client
		@ini_set('max_execution_time', 0);
	    @set_time_limit(0);
	    Overrider::load("Settings");
		$mail  = new \stdClass();
		$mail->subject = _lang('Invoice Payment');
		$mail->invoice = $invoice;
		$mail->transaction = $transaction;
		$mail->method = $transaction->payment_method->name;
		$mail->currency = currency();
		

		try{
			Mail::to($invoice->client->contact_email)->send(new InvoiceReceiptMail($mail));
		}catch (\Exception $e) {
			//Nothing
		}
		
		DB::commit();

		if( $request->ajax() ){
		   $request->session()->flash('success', _lang('Payment was made Sucessfully'));
		   return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Payment was made Sucessfully'),'data'=>$transaction]);	                
		}
    }
	
	public function view_payment(Request $request, $invoice_id){

		$transactions = Transaction::where("invoice_id",$invoice_id)
								   ->where("company_id",company_id())->get();
	
	    if(! $request->ajax()){
		    return view('backend.accounting.invoice.view_payment',compact('transactions'));
		}else{
			return view('backend.accounting.invoice.modal.view_payment',compact('transactions'));
		} 
	}
	
	public function create_email(Request $request, $invoice_id)
    {
		$invoice = Invoice::where("id",$invoice_id)
						  ->where("company_id",company_id())->first();
		
		$client_email = $invoice->client->contact_email; 
		if($request->ajax()){
		    return view('backend.accounting.invoice.modal.send_email',compact('client_email','invoice'));
		} 
	}	
	
	public function send_email(Request $request)
    {
		@ini_set('max_execution_time', 0);
	    @set_time_limit(0);
	    Overrider::load("Settings");
		
		$validator = Validator::make($request->all(), [
			'email_subject' => 'required',
            'email_message' => 'required',
            'contact_email' => 'required',
		]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return back()->withErrors($validator)
							 ->withInput();
			}			
		}
	   
		//Send email
		$subject = $request->input("email_subject");
		$message = $request->input("email_message");
		$contact_email = $request->input("contact_email");
		
		$contact = Contact::where('contact_email',$contact_email)->first();
		$invoice = Invoice::where('id',$request->invoice_id)
						  ->where('company_id', company_id())
						  ->first();
						  
		$currency = currency();
		
		if( $contact ){
			//Replace Paremeter
			$replace = array(
				'{customer_name}'	=> $contact->contact_name,
				'{invoice_no}'		=> $invoice->invoice_number,
				'{invoice_date}' 	=> date('d M,Y', strtotime($invoice->invoice_date)),
				'{due_date}' 		=> date('d M,Y', strtotime($invoice->due_date)),
				'{payment_status}' 	=> _dlang(str_replace('_',' ',$invoice->status)),
				'{grand_total}' 	=> decimalPlace($invoice->grand_total, $currency),
				'{amount_due}' 		=> decimalPlace(($invoice->grand_total - $invoice->paid), $currency),
				'{total_paid}' 		=> decimalPlace($invoice->paid, $currency),
				'{invoice_link}' 	=> url('client/view_invoice/'.md5($invoice->id)),
			);
			
		}
		
		$mail  = new \stdClass();
		$mail->subject = $subject;
		$mail->body = process_string($replace, $message);
		
		try{
			Mail::to($contact_email)->send(new GeneralMail($mail));
		}catch (\Exception $e) {
			if(! $request->ajax()){
			   return back()->with('error', _lang('Sorry, Error Occured !'));
			}else{
			   return response()->json(['result'=>'error','message'=>_lang('Sorry, Error Occured !')]);
			}
		}
		
        if(! $request->ajax()){
           return back()->with('success', _lang('Email Send Sucessfully'));
        }else{
		   return response()->json(['result'=>'success', 'action'=>'update', 'message'=>_lang('Email Send Sucessfully'),'data'=>$contact]);
		}
    }
	
	public function mark_as_cancelled($id){
		$invoice = Invoice::where("id", $id)->where("company_id",company_id())->first();

		if($invoice){
			if($invoice->status == 'Unpaid'){
				$invoice->status = 'Canceled';
				$invoice->save();


					/* 2021-07-15  */
					/* Update PDF in Conta folder */
					$pdfdate_old = $invoice->invoice_date;
					$data['invoice'] = $invoice;
					$data['transactions'] = Transaction::where("invoice_id",$id)
											   ->where("company_id",company_id())->get();
					$data['company'] = CompanySetting::where('company_id',$data['invoice']->company_id)->get();
					
			        $template = $data['invoice']->template;
					if($template == ""){
						$template = "modern";
					}
					if(! file_exists(resource_path("views/backend/accounting/invoice/template/$template.blade.php"))){
			        	$template = 'modern';                             
			        }

					$pdf = PDF::loadView("backend.client_panel.invoice_template.pdf.$template", $data);
			        $pdfpath = public_path("/uploads/file_manager");
			        $pdfdate = $invoice->invoice_date;
			        // $pdfdate = date('Y-m-d');
			        $pdffileName_oldstyle = 'Invoice_'.$pdfdate_old.'_'.$invoice->invoice_number.'.pdf';
			        $pdffileName_old = 'Invoice_'.$pdfdate_old.'_'.$invoice->invoice_number.'_'.$id.'.pdf';
					$pdffileName = 'Invoice_'.$pdfdate.'_'.$invoice->invoice_number.'_'.$id.'.pdf';
					$remove_file = $this->filemanager_store_invoice($pdffileName, $pdfpath, $pdfdate, $pdffileName_old, $pdffileName_oldstyle);
					if($remove_file=="old")
			        	\File::delete($pdfpath . '/' . $pdffileName_old);
			        elseif($remove_file=="oldstyle")
			        	\File::delete($pdfpath . '/' . $pdffileName_oldstyle);
					$pdf->save($pdfpath . '/' . $pdffileName);
					/***/

				return back()->with('success', _lang('Invoice Marked as Canceled'));
			}
		}
		return back();
	}

	public function storno($id){
		$invoice = Invoice::where("id", $id)->where("company_id",company_id())->first();
		$minus = "-";
		if($invoice){

			if($invoice->status != 'Canceled'){

				$st_invoice = $invoice->replicate();
				$st_invoice->invoice_number = get_company_option('invoice_prefix').get_company_option('invoice_starting',1001);
				$st_invoice->status = 'Storno';
				$st_invoice->storno_invoice_id = $id;
				// $st_invoice->storno_invoice_number = $invoice->invoice_number;
				$st_invoice->grand_total = $minus.$st_invoice->grand_total;
				$st_invoice->converted_total = $minus.$st_invoice->converted_total;
				$st_invoice->save();

				//Increment Invoice Starting number
        		increment_invoice_number();

				$invid = $st_invoice->id;
					    	
				$invoiceItem = InvoiceItem::where("invoice_id",$id)->get();
				for($i=0; $i<count($invoiceItem); $i++ )
				{

					$st_invoice_items = $invoiceItem[$i]->replicate();
					$st_invoice_items->invoice_id = $invid;
					$st_invoice_items->sub_total = $minus.$st_invoice_items->sub_total;
					$st_invoice_items->save();
				}	
				return back()->with('success', _lang('Invoice Marked as Storno'));
			}
		}
		return back();
	}
	
	private function filemanager_store_invoice($pdf_filename, $pdf_path, $invoice_date, $pdf_filename_old="", $pdffileName_oldstyle="", $new_invoice_date="") {
		$ro_months = array('ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie');
		$inv_year = date("Y", strtotime($invoice_date));
		$inv_month = date("m", strtotime($invoice_date))-1;

		$filemanager_year = new FileManager();
		$filemanager_year = $filemanager_year->where(["company_id"=>company_id(), 'name'=>$inv_year])->first();
		if($filemanager_year != NULL) {
			$filemanager_month = new FileManager();
			$filemanager_month = $filemanager_month->where(["company_id"=>company_id(), 'parent_id'=>$filemanager_year->id, 'name'=>$ro_months[$inv_month]])->first();
			if($filemanager_month != NULL) {
				$filemanager_fe = new FileManager();
				$filemanager_fe = $filemanager_fe->where(["company_id"=>company_id(), 'parent_id'=>$filemanager_month->id, 'name'=>'Facturi Emise'])->first();
				$fm_parent_id = $filemanager_fe->id;
			}
		}
		
		$new_inv_year = date("Y", strtotime($new_invoice_date));
		$new_inv_month = date("m", strtotime($new_invoice_date))-1;

		$filemanager_year = new FileManager();
		$filemanager_year = $filemanager_year->where(["company_id"=>company_id(), 'name'=>$new_inv_year])->first();
		if($filemanager_year != NULL) {
			$filemanager_month = new FileManager();
			$filemanager_month = $filemanager_month->where(["company_id"=>company_id(), 'parent_id'=>$filemanager_year->id, 'name'=>$ro_months[$new_inv_month]])->first();
			if($filemanager_month != NULL) {
				$filemanager_fe = new FileManager();
				$filemanager_fe = $filemanager_fe->where(["company_id"=>company_id(), 'parent_id'=>$filemanager_month->id, 'name'=>'Facturi Emise'])->first();
				$new_fm_parent_id = $filemanager_fe->id;
			}
		}
		
		$remove = "";
		if(isset($fm_parent_id)) {
			$filemanager = new FileManager();
			$remove = "old";
			if($pdf_filename_old!="") {
				$filemanager = $filemanager->where(["company_id"=>company_id(), 'parent_id'=>$fm_parent_id, 'name'=>$pdf_filename_old])->first();
				if($filemanager == NULL) {
					$remove = "oldstyle";
					$filemanager = FileManager::where(["company_id"=>company_id(), 'parent_id'=>$fm_parent_id, 'name'=>$pdffileName_oldstyle])->first();
				}
			}
			if($pdf_filename_old=="" || $filemanager == NULL) {
				$remove = "";
				$filemanager = new FileManager();
			    $filemanager->name = $pdf_filename;
				// $filemanager->mime_type = mime_content_type($pdf_path . '/' . $pdf_filename);
				$filemanager->mime_type = "application/pdf";
				$filemanager->file = $pdf_filename;
				$filemanager->parent_id = $fm_parent_id;
				$filemanager->company_id = company_id();
				$filemanager->created_by = \Auth::user()->id;
		        $filemanager->save();
			} elseif($remove == "oldstyle" || $pdf_filename != $pdf_filename_old) {
				$filemanager->parent_id = $new_fm_parent_id;
				$filemanager->file = $filemanager->name = $pdf_filename;
				$filemanager->save();
			}
		}
		
		return $remove;
	}
	
	public function export_view()
    {
        return view('backend.accounting.invoice.export');
    }
    
    
    public function export()
    {
    	$invoices = $this->get_table_data(true);
        
        if(!count($invoices)) {
        	// return response()->json(['result'=>'nO-results']);
        }
        
        $fileName = 'onebiz-export-facturi-';
        if(request()->date_type_select==1) {
    		if(request()->date_month !== "" && request()->date_month != -1 && request()->date_month !== NULL)
    			$fileName .= request()->date_month."-".request()->date_year;
    		else
    		    $fileName .= request()->date_year;
    	} else if(request()->date_type_select == 2)
    		$fileName .= request()->start_date."-".request()->end_date;
    	else if(request()->date_type_select == 3) 
			$fileName .= Carbon::now()->subDays(7)->format('Y-m-d')."-".Carbon::now()->format('Y-m-d');
    	else if(request()->date_type_select == 4)
			$fileName .= Carbon::now()->subDays(30)->format('Y-m-d')."-".Carbon::now()->format('Y-m-d');
    	
        $fileName .= '.csv';
	    	
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $contacts = Contact::select('vat_id', 'id')->get()->keyBy('id', 'vat_id')->toArray();
        // $myarray=$invoices->toArray();echo '<pre><font face="verdana" size="2">';print_r($myarray);echo "<a href=\"subl://open?url=file://".urlencode(__FILE__)."&line=".__LINE__."\">".__FILE__.":".__LINE__.'</a></font></pre>'; exit;
        $columns = array('Numar factura', 'Facturat catre', 'Data factura', 'Data scadenta','CUI/CIF/CNP', 'Persoana de contact', 'Taxa', 'Total final', 'Total Platit', 'Stare');

        $callback = function() use($invoices, $columns, $contacts, $fileName) {
        	if(request()->email != '')
        		$file = fopen('php://temp', 'w+');
        	else
            	$file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            


            foreach ($invoices as $invoice) {
                $row['Numar factura']  = $invoice->invoice_number;
                $row['Facturat catre']    = $invoice->contact_name;
                $row['Data factura']  = $invoice->invoice_date;
                $row['Data scadenta']  = $invoice->due_date;
                $row['CUI/CIF/CNP']  = $contacts[$invoice->client_id]['vat_id'] ?? '';
                $row['Persoana de contact']  = $invoice->contact_name;
                $row['Taxa']  = $invoice->tax_total;
                $row['Total final']  = $invoice->grand_total;
                $row['Total Platit']  = $invoice->paid;
                $row['Stare']  = $invoice->status == "Paid" ? "Achitat" : "Neplatit";

                fputcsv($file, array($row['Numar factura'], $row['Facturat catre'], $row['Data factura'], $row['Data scadenta'], $row['CUI/CIF/CNP'], $row['Persoana de contact'], $row['Taxa'], $row['Total final'], $row['Total Platit'], $row['Stare']));
            }
            
            if(request()->email != '') {
            	rewind($file);
            	$email = request()->email;
				$replace = array(
				    '{email}'=> $email,
				);
				Overrider::load("Settings");
				$template = EmailTemplate::where('name','send_exported_invoices')->first();
				$template->body = process_string($replace,$template->body);
				try{
				    $template->file= stream_get_contents($file);
				    $template->fileName= $fileName;
				    Mail::to($email)->send(new PremiumMembershipMail($template));
				}catch (\Exception $e) {
				    //echo $e->getMessage();
				}
            }
            
            fclose($file);
        };
        if(request()->email != '') {
        	$callback();
        	return redirect('invoices/export')->with('message',_lang('Email has been sent to ').request()->email);
        }

        return response()->stream($callback, 200, $headers);
    }
}
