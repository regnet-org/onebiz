<li>
	<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza facturile si proformele"><i class="ti-shopping-cart-full"></i><span>{{ _lang('Sales') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		@if( has_feature('invoice_limit') )
			<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Emite o factura catre un client nou sau existent"><a class="nav-link" href="{{ url('invoices/create') }}">{{ _lang('Add Invoice') }}</a></li>
			<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Afiseaza toate facturile emise"><a class="nav-link" href="{{ url('invoices') }}">{{ _lang('Invoice List') }}</a></li>	
			<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Exporta facturi"><a class="nav-link" href="{{ url('invoices/export') }}">{{ _lang('Exporta facturi') }}</a></li>	
		@endif

		@if( has_feature('quotation_limit') )
			<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Emite o proforma catre un client nou sau existent"><a class="nav-link" href="{{ url('quotations/create') }}">{{ _lang('Add Quotation') }}</a></li>	
			<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Afiseaza toate proformele emise"><a class="nav-link" href="{{ url('quotations') }}">{{ _lang('Quotation List') }}</a></li>	
			<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Exporta proforme"><a class="nav-link" href="{{ url('quotations/export') }}">{{ _lang('Exporta Proforme') }}</a></li>	
		@endif
	</ul>
</li>

@if( has_feature('file_manager') )
<li>
	<a href="{{ url('file_manager') }}" data-toggle="tooltip" data-placement="top" title="Urcati fisiere pentru contabilitate sau juridice"><i class="ti-folder"></i><span>{{ _lang('File Manager') }}</span></a>
</li>
@endif

<li>
	<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza-ti clientii, lead-urile si proiectele"><i class="ti-id-badge"></i><span>{{ _lang('Customers') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level">
	@if( has_feature( 'contacts_limit' ) )
	<li>
	<a href="javascript: void(0);"><i class="ti-id-badge"></i><span>{{ _lang('Customers') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Afiseaza toti clientii inregistrati"><a class="nav-link" href="{{ url('contacts') }}">{{ _lang('Contacts List') }}</a></li>
		<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Adauga un client nou"><a class="nav-link" href="{{ url('contacts/create') }}">{{ _lang('Add New') }}</a></li>	
		<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Afiseaza categoriile de clienti"><a class="nav-link" href="{{ url('contact_groups') }}">{{ _lang('Contact Group') }}</a></li>	
	</ul>
	</li>
	@endif

	<li class="nav-item">
	<a href="{{ route('leads.index') }}"><i class="fas fa-tty" data-toggle="tooltip" data-placement="top" title="Gestioneaza potentialii clienti"></i><span>{{ _lang('Leads') }}</span></a>
	</li>
	



		@if( has_feature('project_management_module') )


		{{-- <li><a href="{{ route('projects.index') }}"><i class="ti-briefcase"></i><span>{{ _lang('Projects') }}</span></a></li>
		<li><a href="{{ route('tasks.index') }}"><i class="ti-check-box"></i><span>{{ _lang('Tasks') }}</span></a></li> --}}


	<li>
	<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza proiectele si task-urile"><i class="ti-briefcase"></i><span>{{ _lang('Projects') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Creeaza sau modifica un proiect"><a class="nav-link" href="{{ route('projects.index') }}">{{ _lang('Projects') }}</a></li>
		<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Creeaza sau modifica un task"><a class="nav-link" href="{{ route('tasks.index') }}">{{ _lang('Tasks') }}</a></li>

		@if(get_option('live_chat') == 'enabled' && has_feature('live_chat') )
			<li class="nav-item">
		       <a class="nav-link" href="{{ url('live_chat') }}">{{ _lang('Messenger') }}<span class="chat-notification {{ unread_message_count() > 0 ? 'show' : 'hidden' }}">{{ unread_message_count() }}</span></a>
			</li>
		@endif
	</ul>
</li>


</ul>

</li>



@endif

<li>
	<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza produsele sau serviciile oferite"><i class="ti-shopping-cart"></i><span>{{ _lang('Products') }}&{{ _lang('Service') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level">
		
		<li>
			<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza serviciile oferite"><i class="ti-agenda"></i><span>{{ _lang('Service') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('services/create') }}">{{ _lang('Add New') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('services') }}">{{ _lang('Service List') }}</a></li>	
			</ul>
		</li>

		<li>
			<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza produsele vandute"><i class="ti-shopping-cart"></i><span>{{ _lang('Products') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('products/create') }}">{{ _lang('Add New') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('products') }}">{{ _lang('Product List') }}</a></li>	
			</ul>
		</li>

		<li>
			<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza furnizorii"><i class="ti-truck"></i><span>{{ _lang('Supplier') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('suppliers/create') }}">{{ _lang('Add New') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('suppliers') }}">{{ _lang('Supplier List') }}</a></li>	
			</ul>
		</li>

		@if( has_feature('inventory_module') )
		<li>
			<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza achizitiile"><i class="ti-bag"></i><span>{{ _lang('Purchase') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('purchase_orders') }}">{{ _lang('Purchase Orders') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('purchase_orders/create') }}">{{ _lang('Create Purchase Order') }}</a></li>	
			</ul>
		</li>

		<li>
			<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza retururile"><i class="ti-back-left"></i><span>{{ _lang('Return') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('purchase_returns') }}">{{ _lang('Purchase Return') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('sales_returns') }}">{{ _lang('Sales Return') }}</a></li>	
			</ul>
		</li>
		@endif
	</ul>
</li>


<li>
	<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Gestioneaza tranzactiile si conturile bancare"><i class="ti-receipt"></i><span>{{ _lang('Transactions') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level">

		<li>
			<a href="javascript: void(0);"><i class="ti-receipt"></i><span>{{ _lang('Transactions') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('income') }}">{{ _lang('Income/Deposit') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('expense') }}">{{ _lang('Expense') }}</a></li>	
				<li class="nav-item"><a class="nav-link" href="{{ url('transfer/create') }}">{{ _lang('Transfer') }}</a></li>	
				<li class="nav-item"><a class="nav-link" href="{{ url('income/calendar') }}">{{ _lang('Income Calendar') }}</a></li>	
				<li class="nav-item"><a class="nav-link" href="{{ url('expense/calendar') }}">{{ _lang('Expense Calendar') }}</a></li>	
			</ul>
		</li>

		@if( has_feature('recurring_transaction') )
		<li>
			<a href="javascript: void(0);"><i class="ti-wallet"></i><span>{{ _lang('Recurring Transaction') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('repeating_income/create') }}">{{ _lang('Add Repeating Income') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('repeating_income') }}">{{ _lang('Repeating Income List') }}</a></li>	
				<li class="nav-item"><a class="nav-link" href="{{ url('repeating_expense/create') }}">{{ _lang('Add Repeating Expense') }}</a></li>	
				<li class="nav-item"><a class="nav-link" href="{{ url('repeating_expense') }}">{{ _lang('Repeating Expense List') }}</a></li>	
			</ul>
		</li>
		@endif

		<li>
			<a href="javascript: void(0);"><i class="ti-credit-card"></i><span>{{ _lang('Accounts') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('accounts') }}">{{ _lang('List Account') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('accounts/create') }}">{{ _lang('Add New Account') }}</a></li>	
			</ul>
		</li>
	</ul>
</li>







<li>
	<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Verifica rapoarte financiare"><i class="ti-bar-chart"></i><span>{{ _lang('Reports') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/account_statement') }}">{{ _lang('Account Statement') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/day_wise_income') }}">{{ _lang('Detail Income Report') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/date_wise_income') }}">{{ _lang('Date Wise Income') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/day_wise_expense') }}">{{ _lang('Detail Expense Report') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/date_wise_expense') }}">{{ _lang('Date Wise Expense') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/transfer_report') }}">{{ _lang('Transfer Report') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/income_vs_expense') }}">{{ _lang('Income VS Expense') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/report_by_payer') }}">{{ _lang('Report By Payer') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/report_by_payee') }}">{{ _lang('Report By Payee') }}</a></li>	
	</ul>
</li>

<li>
	<a href="{{ url('regcomert') }}"><i class="ti-files"></i><span>{{ _lang('RegComert Link') }}</span></a>
</li>


<li>
	<a href="javascript: void(0);" data-toggle="tooltip" data-placement="top" title="Seteaza datele firmei si ale contului"><i class="ti-settings"></i><span>{{ _lang('Settings') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Setari generale privind afacerea"><a class="nav-link" href="{{ url('company/general_settings') }}">{{ _lang('Company Settings') }}</a></li>
		@if( has_feature('project_management_module') )
			<li class="nav-item" data-toggle="tooltip" data-placement="top" title="Setari privind lead sau task"><a class="nav-link" href="{{ url('company/crm_settings') }}">{{ _lang('CRM Settings') }}</a></li>
		@endif

		@if( has_feature('staff_limit') )
		<li class="nav-item">
			<a href="javascript: void(0);">{{ _lang('Staffs') }}<span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			<ul class="nav-second-level" aria-expanded="false">
				<li class="nav-item"><a class="nav-link" href="{{ url('staffs') }}">{{ _lang('All Staff') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('staffs/create') }}">{{ _lang('Add New') }}</a></li>
				<li class="nav-item"><a class="nav-link" href="{{ url('permission/control') }}">{{ _lang('Access Control') }}</a></li>		
			</ul>
		</li>
		@endif




		<li class="nav-item">
		<a href="javascript: void(0);">Alte Setari</a>
		<ul class="nav-second-level" aria-expanded="false">

		<li class="nav-item"><a class="nav-link" href="{{ url('company_email_template') }}">{{ _lang('Email Template') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('chart_of_accounts') }}">{{ _lang('Income & Expense Types') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('payment_methods') }}">{{ _lang('Payment Methods') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('product_units') }}">{{ _lang('Product Unit') }}</a></li>	
		<li class="nav-item"><a class="nav-link" href="{{ url('taxs') }}">{{ _lang('Tax Settings') }}</a></li>	
		</ul>
		</ul>
		

		<li class="nav-item">
		<a href="https://www.onebiz.ro/site/faq" target="_blank" data-toggle="tooltip" data-placement="top" title="Afla cum functioneaza ONEBIZ"><i class=" ti-help-alt"></i><span>Ajutor</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
		</li>
	
	

		<?php /* ?>
		<li class="nav-item"><a class="nav-link" href="{{ url('bt_auth') }}">{{ _lang('BT settings') }}</a></li> --}}
		<?php */ ?>
	</ul>
</li>
