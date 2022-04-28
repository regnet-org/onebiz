<?php

namespace App\Http\Middleware;

use Illuminate\Http\Response;
use Closure;
use Auth;

class Company
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	$user = Auth::user();
		$user_type = $user->user_type;
		$user_id = $user->id;
		
		if($user_type == 'user' || $user_type == 'staff'){
			$route_name = \Request::route()->getName();
			if($route_name=='invoice.view') //convert non-existent permission
				$route_name='invoices.show';
			if($route_name=='quotation.view') //convert non-existent permission
				$route_name='quotations.show';

			/** If User Type = Staff **/
			if( $route_name != '' && $route_name != 'file_manager.download.all' && $user_type != 'user' && $route_name != 'file_manager.preview'){
				if($route_name=='file_manager.search')
					$route_name = 'file_manager.index';
				if(explode(".",$route_name)[1] == "update"){
					$route_name = explode(".",$route_name)[0].".edit";
				}else if(explode(".",$route_name)[1] == "store"){
					$route_name = explode(".",$route_name)[0].".create";
				}
				
				if( ! has_permission($route_name, $user_id)){
					if( ! $request->ajax()){
					   return back()->with('error',_lang('Permission denied !'));
					}else{
					   return new Response('<h4 class="text-center red">'._lang('Permission denied !').'</h4>');
					}
				}
			}

		}else{
			if( ! $request->ajax()){
			    return back()->with('error',_lang('Permission denied !'));
			}else{
				return new Response('<h5 class="text-center red">'._lang('Permission denied !').'</h5>');
			}
		}

		if(has_membership_system() == 'enabled'){
			if( membership_validity() < date('Y-m-d')){
				return redirect('membership/extend')->with('message',_lang('Please make your membership payment for further process !'));
			}
		}
			
        return $next($request);
    }
}
