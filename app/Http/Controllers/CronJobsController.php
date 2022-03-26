<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use App\RepeatTransaction;
use App\User;
use App\EmailTemplate;
use App\Company;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlertNotificationMail;
use App\Utilities\Overrider;
use DB;

use App\Mail\PremiumMembershipMail;
use App\FileManager;
use App\AccessControl;

class CronJobsController extends Controller
{
	
    /**
     * Show the application CronJobs.
     *
     * @return \Illuminate\Http\Response
     */
    public function run()
    {
		@ini_set('max_execution_time', 0);
		@set_time_limit(0);
		
		/** Update Currency Exchange Rate **/
		update_currency_exchange_rate(true);
		
		/** Process Repeat Transactions **/
		$date = date("Y-m-d");
		$repeat_transaction = RepeatTransaction::where('trans_date',$date)
		                                       ->where('status',0)
		                                       ->get();
											   
		foreach($repeat_transaction as $transaction){
			if($transaction->type == 'income'){
				$trans = new Transaction();
				$trans->trans_date = $transaction->trans_date;
				$trans->account_id = $transaction->account_id;
				$trans->chart_id = $transaction->chart_id;
				$trans->type = 'income';
				$trans->dr_cr = 'cr';
				$trans->amount = $transaction->amount;
				$trans->base_amount = $transaction->base_amount;
				$trans->payer_payee_id = $transaction->payer_payee_id;
				$trans->payment_method_id = $transaction->payment_method_id;
				$trans->reference = $transaction->reference;
				$trans->note = $transaction->note;
				$trans->company_id = $transaction->company_id;
				$trans->save();
				
				$transaction->trans_id = $trans->id;
				$transaction->status = 1;
				$transaction->save();		
			}else if($transaction->type == 'expense'){
				$trans = new Transaction();
				$trans->trans_date = $transaction->trans_date;
				$trans->account_id = $transaction->account_id;
				$trans->chart_id = $transaction->chart_id;
				$trans->type = 'expense';
				$trans->dr_cr = 'dr';
				$trans->amount = $transaction->amount;
				$trans->base_amount = $transaction->base_amount;
				$trans->payer_payee_id = $transaction->payer_payee_id;
				$trans->payment_method_id = $transaction->payment_method_id;
				$trans->reference = $transaction->reference;
				$trans->note = $transaction->note;
				$trans->company_id = $transaction->company_id;
				$trans->save();
				
				$transaction->trans_id = $trans->id;
				$transaction->status = 1;
				$transaction->save();
			}
		}
		
		/** Send Alert Notification to User before expiry package **/
		$days_before = 14;
		$user_list = DB::select("SELECT users.*, companies.valid_to FROM users JOIN companies ON users.company_id = companies.id WHERE DATEDIFF(companies.valid_to, CURDATE()) <= $days_before AND companies.last_email IS NULL AND users.user_type='user'");
        
		if (count($user_list) > 0) {
            foreach ($user_list as $user) {
				/** Replace Paremeter **/
				$replace = array(
					'{name}'     => $user->name,
					'{email}'    => $user->email,
					'{valid_to}' => date('d M, Y', strtotime($user->valid_to)),
				);
				
				//Send email Confrimation
				Overrider::load("Settings");
				$template = EmailTemplate::where('name','alert_notification')->first();
				$template->body = process_string($replace, $template->body);
				
				try{
					Mail::to($user->email)->send(new AlertNotificationMail($template));
				}catch (\Exception $e) {
					//Noting
				}	
                $company = Company::find($user->company_id);
                $company->last_email = date('Y-m-d');
				$company->save();
            }

        }

        echo 'Scheduled task runs successfully';
	
    }
    
    public function FilemanagerNoUploadNotification()
    {
		@ini_set('max_execution_time', 0);
		@set_time_limit(0);
		
		if(request()->{'show-clients'}!=1 && request()->run!=1 && request()->debug!=1 && (int)date('d') != 6) // this IP for local testing only to run any time
    		return;
    	
		$folder_names = ['Altele (bonuri, chitante etc.)', 'Extrase Cont', 'Facturi Emise', 'Facturi Platite'];
		$ro_months = array('ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie');
		$current_month_index = (int)date('m')-1;
		$current_month_ro = $ro_months[$current_month_index];
		
		$conta2_user = User::where(['email'=>'conta2@regnet.ro'])->first();
		$conta2_client_ids = collect(preg_split("/\|/", trim($conta2_user->company_ids, "|")))->reject(function($id) use($conta2_user){ return $conta2_user->company_id==$id; }); //exclude own company_id
	
		$conta2_filemanager_client_ids = AccessControl::where(['permission'=>'file_manager.index', 'user_id'=>$conta2_user->id])->whereIn('staff_company_id', $conta2_client_ids)->get()->pluck('staff_company_id');
		
		$conta2_companies = User::select('id', 'name', 'email', 'company_id')->whereIn('company_id', $conta2_filemanager_client_ids)->get();
		
		if(request()->{'show-clients'}==1) {
			echo "Clientii care sunt pe conta si au dat acces la File Manager:<br />";
			foreach ($conta2_companies as $i=>$company) {
				echo ($i+1).". ".$company->name.", ".$company->email."<br />";
			}
			exit();
		}

		$count = 0;
		foreach ($conta2_companies as $company) {
			$filemanager_conta =  FileManager::where(["company_id"=>$company->company_id, 'name'=>"Contabilitate"])->first();
			if(!$filemanager_conta)
				continue;
			$filemanager_year =  FileManager::where(["company_id"=>$company->company_id, 'parent_id'=>$filemanager_conta->id, 'name'=>date('Y')])->first();
			if(!$filemanager_year)
				continue;
			$filemanager_month =  FileManager::where(["company_id"=>$company->company_id, 'parent_id'=>$filemanager_year->id, 'name'=>$current_month_ro])->first();
			if(!$filemanager_month)
				continue;
			$filemanager_month_subfolders =  FileManager::where(["company_id"=>$company->company_id, 'parent_id'=>$filemanager_month->id])->whereIn('name', $folder_names)->get()->pluck('name', 'id');
			if(!$filemanager_month_subfolders)
				continue;
			$filemanager_month_subfolder_content =  FileManager::where(["company_id"=>$company->company_id])->whereIn('parent_id', $filemanager_month_subfolders->keys())->get()->pluck('name', 'parent_id');
			$empty_dirs = [];
			foreach ($filemanager_month_subfolders as $key => $value) {
				if(!isset($filemanager_month_subfolder_content[$key]))
					$empty_dirs[$key] = $value;
					
			}
			if(count($empty_dirs)) {
				$email = $company->email;
				$replace = array(
				    '{email}'=> $email,
				    '{client_name}'=>$company->name,
				    '{empty_directories}'=>implode(", ", $empty_dirs),
				);
				Overrider::load("Settings");
				$template = EmailTemplate::where('name','missing_filemanager_upload_notification')->first();
				$template->body = process_string($replace,$template->body);
				$count++;
				echo "${count}. <strong>${email}</strong><br />";
				if(request()->debug) {
					echo $template->subject;
					echo $template->body;
					echo "<hr />";
				}
				try{
					if(request()->run==1 && !request()->debug && (int)date('d') == 6) {
						if(request()->ip()!="192.168.1.3" && request()->ip()!="82.78.230.101") {
				    		Mail::to($email)->send(new PremiumMembershipMail($template));
						}
					}
				}catch (\Exception $e) {
				    //echo $e->getMessage();
				}
			}
		}
		if(!request()->debug)
			echo "Done! &nbsp;";return;
	}
}
