<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FileManager;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
// use Illuminate\Support\Facades\Storage;
use File;
use App\AccessControl;
class FileManagerController extends Controller
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
                if( ! has_feature( 'file_manager' ) ){
                    return redirect('membership/extend')->with('message', _lang('Your Current package not support this feature. You can upgrade your package !'));
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
    public function index($parent_id = '')
    {
        $this->giveDownloadPermissionToStaff();
        if(request()->reload) {
            $filemanagers = FileManager::where("company_id",0)
                                       ->where('parent_id',-1)
                                       ->orderBy("name","asc")
                                       ->get();
            if($filemanagers->count())
                $filemanagers->each->delete();

            foreach (glob(storage_path()."/uploads/file_manager/modele_acte/*") as $filename) {
                $filemanager = new FileManager();
                $filemanager->file = basename(dirname($filename))."/".basename($filename);
                $filemanager->name = basename($filename);
                $filemanager->mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filename);;
                $filemanager->is_dir = 'no';
                $filemanager->parent_id = -1;
                $filemanager->company_id = 0;
                $filemanager->created_by = 0;
                $filemanager->save();
            }
        }
        $ro_months = array('ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie');
        $data_allow_upload_after_10 = 1;
        if($parent_id==''){
            $back = false;
            $filemanagers = FileManager::where("company_id",company_id())
                                       ->where('parent_id',null)
                                       ->orderBy("name","asc")
                                       ->get();
            
            /*bx 2020_11_25_101947 */
            $subfolders = array('Facturi Emise', 'Facturi Platite', 'Extrase Cont', 'Altele (bonuri, chitante etc.)');
            $current_month = date("m", strtotime("this month"))-1;
            $next_month = date("m", strtotime("next month"))-1;
            $create_months = array($ro_months[$current_month], $ro_months[$next_month]);
            $year = date("Y", strtotime("next month"));
            $fm = $filemanagers->pluck('name', 'id')->toArray();

            \DB::beginTransaction();
            $new_parent = 0;
            foreach (array("Contabilitate", "Legal") as $test_cl) {
                if(!in_array($test_cl, $fm)) {
                    $filemanager = new FileManager();
                    $filemanager->name = $test_cl;
                    $filemanager->is_dir = 'yes';
                    $filemanager->company_id = company_id();
                    $filemanager->created_by = Auth::user()->id;
                    $filemanager->save();
                    if($test_cl=="Contabilitate")
                        $cl_parent_id = $filemanager->id;
                    elseif($test_cl=="Legal")
                        $l_parent_id = $filemanager->id;
                    $new_parent = 1;
                } elseif($test_cl=="Legal") {
                    $l_parent_id = array_search("Legal", $fm);
                } elseif($test_cl=="Contabilitate") {
                    $cl_parent_id = array_search("Contabilitate", $fm);
                }
            }
            
            $test_ma_as = FileManager::where("company_id",company_id())
                               ->where('parent_id',$l_parent_id)
                               ->orderBy("name","asc")
                               ->get()->pluck('name', 'id')->toArray();
            foreach (array("Modele acte", "Acte semnate") as $test_lm) {
                if(!in_array($test_lm, $test_ma_as)) {
                    $filemanager = new FileManager();
                    $filemanager->name = $test_lm;
                    $filemanager->is_dir = 'yes';
                    $filemanager->parent_id = $l_parent_id;
                    $filemanager->company_id = company_id();
                    $filemanager->created_by = Auth::user()->id;
                    $filemanager->save();
                }
            }
            

        $fm_start_pattern = date('Y', strtotime(auth()->user()->created_at));
        $fm_contabilitate = FileManager::where("company_id",company_id())
                                               ->where('parent_id',null)
                                               ->where('name','=', "Contabilitate")
                                               ->orderBy("name","asc")
                                               ->first()->id;
        $current_year = date('Y');
        $fm_current_year = FileManager::where("company_id",company_id())
                                               ->where('parent_id',$fm_contabilitate)
                                               ->where('name',$current_year)
                                               ->orderBy("name","asc")
                                               ->get()->pluck('name', 'id')->toArray();
        $fm_months = [];
        if($fm_current_year) {
            $max_year_parent_id = array_search(max($fm_current_year), $fm_current_year);
            $fm_months = FileManager::where("company_id",company_id())
                                               ->where('parent_id',$max_year_parent_id)
                                               ->orderBy("name","asc")
                                               ->get()->pluck('name', 'id')->toArray();
        } else {
            $filemanager = new FileManager();
            $filemanager->name = $current_year;
            $filemanager->is_dir = 'yes';
            $filemanager->parent_id = $fm_contabilitate;
            $filemanager->company_id = company_id();
            $filemanager->created_by = Auth::user()->id;
            $filemanager->save();
            $max_year_parent_id = $filemanager->id;
        }
            if(count($fm_months) != count($ro_months)) {
                foreach ($ro_months as $ro_month_k => $ro_month) {
                    if(in_array($ro_month, $fm_months) || ($ro_month_k+1)<date('m'))
                        continue;

                    $fm_reload = true;
                    $filemanager = new FileManager();
                    $filemanager->name = $ro_month;
                    $filemanager->is_dir = 'yes';
                    $filemanager->parent_id = $max_year_parent_id;
                    $filemanager->company_id = company_id();
                    $filemanager->created_by = Auth::user()->id;
                    $filemanager->save();
                    $month_parent_id = $filemanager->id;
                    
                    foreach ($subfolders as $sf) {
                        $filemanager = new FileManager();
                        $filemanager->name = $sf;
                        $filemanager->is_dir = 'yes';
                        $filemanager->parent_id = $month_parent_id;
                        $filemanager->company_id = company_id();
                        $filemanager->created_by = Auth::user()->id;
                        $filemanager->save();
                    }
                }
            }

        // echo "test"; return;
            \DB::commit();
                
            if($new_parent) { //reload
                $filemanagers = FileManager::where("company_id",company_id())
                                       ->where('parent_id',null)
                                       ->orderBy("name","asc")
                                       ->get();
            }
            /*bx 2020_11_25_101947 */
        }else{
            $back = true;
            $parent_id = decrypt($parent_id);
            $modele_acte = request()->modele_acte;
            $filemanagers = FileManager::where("company_id",company_id())
                                       ->where('parent_id',$parent_id)
                                       ->orWhere(function($query) use ($modele_acte){
                                         if($modele_acte)
                                            $query->where('parent_id',-1);
                                         
                                       })
                                       ->orderBy("name","asc")
                                       ->get();
        

            $all_filemanagers = FileManager::where("company_id",company_id())
                                           ->orderBy("name","asc")
                                           ->select('id', 'parent_id', 'name')
                                           ->get()->keyBy('id')->toArray();
            
            function traverse($all_filemanagers, $parent_id) {
                foreach ($all_filemanagers as $key => $value) {
                    if($key == $parent_id) {
                        if(isset($value['parent_id']) && $value['parent_id']!='') {
                            return [$value['parent_id']=>$value['name']];
                        }
                    }
                }
                return null;
            }
            $active_path = [];

            $allow_upload_after_10 = \App\Company::find(company_id())->allow_upload_after_10;
            if($allow_upload_after_10==0) {
                $tmp_parent_id = $parent_id2= $parent_id;
                while(1) {
                    $active_path_a = traverse($all_filemanagers, $parent_id2);
                    if($active_path_a !== NULL) {
                        $parent_id2 = key($active_path_a);
                        $active_path = array_merge($active_path, $active_path_a);
                        continue;
                    }
                    break;
                }

                if(isset($active_path) && count($active_path)) {
                    if(isset($active_path[0])) {
                        if(end($active_path)<date('Y')) { //older year than current year
                            if(date('m')=="1" && (end($active_path)<date('Y') && date('Y')-1)) {
                                if(count($active_path)>=2 && in_array($active_path[count($active_path)-2], $ro_months)) { //we are in XXXX year and inside a valid month name
                                    $selected_month = $active_path[count($active_path)-2];
                                    if((array_search($selected_month, $ro_months)+1) < 12)
                                        $data_allow_upload_after_10 = 0;
                                    elseif(intval(date('d')) > 10)
                                        $data_allow_upload_after_10 = 0;
                                }
                            } else
                                $data_allow_upload_after_10 = 0;
                        } elseif(end($active_path)==date('Y')) {
                            if(count($active_path)>=2 && in_array($active_path[count($active_path)-2], $ro_months)) { //we are in XXXX year and inside a valid month name
                                $selected_month = $active_path[count($active_path)-2];
                                
                                if((array_search($selected_month, $ro_months)+1) == intval(date('m'))-1 || (array_search($selected_month, $ro_months)+1)==1 && (intval(date('m'))-1)==0) { //firts condition is for opre onth second is for ianuary/december
                                    if(intval(date('d')) > 10)
                                        $data_allow_upload_after_10 = 0;
                                } elseif((array_search($selected_month, $ro_months)+1) < intval(date('m'))) {
                                    $data_allow_upload_after_10 = 0;
                                }
                                elseif((array_search($selected_month, $ro_months)+1) == intval(date('m'))) {
                                    if(intval(date('d')) > 10)
                                        $data_allow_upload_after_10 = 0;
                                }
                            }
                        }
                    }
                }
            }
        
        }   
        
        $fm_breadcumbs = [];
        if($parent_id!='') {
            function getBreadcrumb($categories, $categoryId) {

                $thisCat = $categories[$categoryId];

                if ($thisCat['parent_id'] != 0)
                {
                   return array_merge(getBreadcrumb($categories, $thisCat['parent_id']), array(array('name'=>$thisCat['name'], 'id'=>$thisCat['id'])));
                }   
                else
                {
                   return array(array('name'=>$thisCat['name'], 'id'=>$thisCat['id']));
                }
            }

            $filemanagers3 = FileManager::get()->keyBy('id');
            $fm_breadcumbs = getBreadcrumb($filemanagers3, $parent_id);
            // $myarray=$fm_breadcumbs;echo '<pre><font face="verdana" size="2">';print_r($myarray);echo "<a href=\"subl://open?url=file://".urlencode(__FILE__)."&line=".__LINE__."\">".__FILE__.":".__LINE__.'</a></font></pre>'; exit;
            
        }
        if(\Auth::user()->user_type == "staff")
            $data_allow_upload_after_10 = 1;
        $exclude_file_upload_permission = array('conta@regnet.ro', 'conta2@regnet.ro'); //allow upload anytime
        $user_conta = \App\User::where('company_id', company_id())->whereIn('email', $exclude_file_upload_permission)->first();
        if($user_conta)
            $data_allow_upload_after_10 = 1;
        
        return view('backend.file_manager.list',compact('filemanagers','back', 'data_allow_upload_after_10', 'fm_breadcumbs'));
    }
    
    public function search(Request $request) {
        $data_allow_upload_after_10 = 0;
        $back = false;
        $fm_breadcumbs = [];
        $filemanagers = new FileManager;
        $filemanagers = $filemanagers->where(['company_id'=>company_id()], ['is_dir'=>'no']);
        $search = 0;
        if($request->invoice_number) {
            $search = 1;
            $filemanagers = $filemanagers->where('file', 'like', '%'.$request->invoice_number.'%');
        }
        if($request->client_id) {
            // $invoice = new \App\Invoice;
            // exit; //)->where(['client_id'=>$request->client_id])->get();
            // exit;
            $clients = (new \App\Invoice)->where(['client_id'=>request()->client_id])->select('id')->pluck('id');
            if(count($clients)) {
                $search = 1;
                $filemanagers = $filemanagers->where(function($query) {
                    foreach ((new \App\Invoice)->where(['client_id'=>request()->client_id])->select('id')->pluck('id') as $value) {
                        $query->orWhere('file', 'like', "%_".$value.".pdf");
                    }
                });
            } else {
                $filemanagers = $filemanagers->where('file', '=', "no-result-found");
            }
        }
        if($search == 0)
            return redirect()->route('file_manager.index');
        // if($request->invoice_date)
            // $filemanagers = $filemanagers->where('file', '=', $request->invoice_date);
        $filemanagers = $filemanagers->get();
        
        return view('backend.file_manager.list',compact('filemanagers','back', 'data_allow_upload_after_10', 'fm_breadcumbs'));
       }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $parent_id='')
    {
        $parent_directory = FileManager::where('is_dir','yes')
                                       ->where('company_id',company_id())
                                       ->get();
        if( ! $request->ajax()){
           return view('backend.file_manager.create',compact('parent_directory','parent_id'));
        }else{
           return view('backend.file_manager.modal.create',compact('parent_directory','parent_id'));
        }
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_folder(Request $request, $parent_id='')
    {
        $parent_directory = FileManager::where('is_dir','yes')
                                       ->where('company_id',company_id())
                                       ->get();
        if( ! $request->ajax()){
           return view('backend.file_manager.create_folder',compact('parent_directory','parent_id'));
        }else{
           return view('backend.file_manager.modal.create_folder',compact('parent_directory','parent_id'));
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
        $max_size = get_option('file_manager_max_upload_size',2) * 1024;
        $supported_file_types = get_option('file_manager_file_type_supported','png,jpg,jpeg');
        
        $validator = Validator::make($request->all(), [
            // 'name' => 'required|max:64',
            // 'file' => "required|file|max:$max_size|mimes:$supported_file_types",
            'file.*' => "required|file|max:$max_size|mimes:$supported_file_types",
        ],
        [
            'mimes' => 'File type is not supported',
        ]);
        
        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('file_manager.create')
                            ->withErrors($validator)
                            ->withInput();
            }           
        }
        
        if($this->is_duplicate_file($request->input('name'), $request->input('parent_id'))){
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>array('error'=> _lang('File Name already exists !'))]);
            }else{
                return back()->withErrors($validator)
                             ->withInput();
            }   
        }

        if($request->hasfile('file'))
        {
            foreach ($request->file('file') as $file) {
                $file_name = time().'_'.$file->getClientOriginalName();
                $file->move(storage_path()."/uploads/file_manager/", $file_name);
                
                $filemanager = new FileManager();
                // $filemanager->name = $request->input('name');
                $filemanager->name = $file_name;
                $filemanager->mime_type = mime_content_type(storage_path().'/uploads/file_manager/'.$file_name);
                $filemanager->file = $file_name;
                $filemanager->parent_id = $request->input('parent_id');
                $filemanager->company_id = company_id();
                $filemanager->created_by = Auth::user()->id;
            
                $filemanager->save();
            }
        }

        if(! $request->ajax()){
           return redirect()->route('file_manager.create')->with('success', _lang('Saved Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Sucessfully'),'data'=>$filemanager]);
        }
        
   }
   
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_folder(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:64',
        ]);
        
        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('file_manager.create_folder')
                            ->withErrors($validator)
                            ->withInput();
            }           
        }
        
        if($this->is_duplicate_folder($request->input('name'), $request->input('parent_id'))){
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>array('error'=> _lang('Folder Name already exists !'))]);
            }else{
                return back()->withErrors($validator)
                             ->withInput();
            }   
        }
            
        
        $filemanager = new FileManager();
        $filemanager->name = $request->input('name');
        $filemanager->is_dir = 'yes';
        $filemanager->parent_id = $request->input('parent_id');
        $filemanager->company_id = company_id();
        $filemanager->created_by = Auth::user()->id;
    
        $filemanager->save();

        if(! $request->ajax()){
           return back()->with('success', _lang('Saved Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Sucessfully'),'data'=>$filemanager]);
        }
        
   }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $parent_directory = FileManager::where('is_dir','yes')
                                       ->where('company_id',company_id())
                                       ->get();
                                       
        $filemanager = FileManager::where("id",$id)
                                  ->where("company_id",company_id())->first();
        if(! $request->ajax()){
           return view('backend.file_manager.edit',compact('filemanager','id','parent_directory'));
        }else{
           return view('backend.file_manager.modal.edit',compact('filemanager','id','parent_directory'));
        }  
        
    }
    
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit_folder(Request $request, $id)
    {
        $parent_directory = FileManager::where('is_dir','yes')
                                       ->where('company_id',company_id())
                                       ->where('id','!=',$id)
                                       ->get();
                                       
        $filemanager = FileManager::where("id",$id)
                                  ->where("company_id",company_id())->first();
        if(! $request->ajax()){
           return view('backend.file_manager.edit_folder',compact('filemanager','id','parent_directory'));
        }else{
           return view('backend.file_manager.modal.edit_folder',compact('filemanager','id','parent_directory'));
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
        $max_size = get_option('file_manager_max_upload_size',2) * 1024;
        $supported_file_types = get_option('file_manager_file_type_supported','png,jpg,jpeg');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'file' => "nullable|file|max:$max_size|mimes:$supported_file_types",
        ],
        [
            'mimes' => 'File type is not supported',
        ]);
        
        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('file_manager.edit', $id)
                            ->withErrors($validator)
                            ->withInput();
            }           
        }
        
        if($this->is_duplicate_file($request->input('name'), $request->input('parent_id'), $id)){
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>array('error'=> _lang('File Name already exists !'))]);
            }else{
                return back()->withErrors($validator)
                             ->withInput();
            }   
        }
    
        if($request->hasfile('file'))
        {
            $file = $request->file('file');
            $file_name = time().$file->getClientOriginalName();
            $file->move(storage_path()."/uploads/file_manager/", $file_name);
        }   
        
        $filemanager = FileManager::where("id",$id)
                                  ->where("company_id",company_id())->first();
        $filemanager->name = $request->input('name');
        if($request->hasfile('file')){
            $filemanager->file = $file_name;
            $filemanager->mime_type = mime_content_type(storage_path().'/uploads/file_manager/'.$file_name);
        }
        $filemanager->parent_id = $request->input('parent_id');
        $filemanager->company_id = company_id();
        $filemanager->created_by = Auth::user()->id;
    
        $filemanager->save();
        
        if(! $request->ajax()){
           return redirect()->route('file_manager.index')->with('success', _lang('Updated Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Sucessfully'),'data'=>$filemanager]);
        }
        
    }
    
     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_folder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:64',
        ]);
        
        if ($validator->fails()) {
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('file_manager.edit', $id)
                            ->withErrors($validator)
                            ->withInput();
            }           
        }
        
        if($this->is_duplicate_folder($request->input('name'), $request->input('parent_id'), $id)){
            if($request->ajax()){ 
                return response()->json(['result'=>'error','message'=>array('error'=> _lang('Folder Name already exists !'))]);
            }else{
                return back()->withErrors($validator)
                             ->withInput();
            }   
        }
    
        $filemanager = FileManager::where("id",$id)
                                  ->where("company_id",company_id())->first();
        $filemanager->name = $request->input('name');
        $filemanager->parent_id = $request->input('parent_id');
        $filemanager->company_id = company_id();
        $filemanager->created_by = Auth::user()->id;
    
        $filemanager->save();
        
        if(! $request->ajax()){
           return back()->with('success', _lang('Updated Sucessfully'));
        }else{
           return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Sucessfully'),'data'=>$filemanager]);
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
        $filemanager = FileManager::where("id",$id)
                                  ->where("company_id",company_id())
                                  ->where("created_by", Auth::user()->id)
                                  ->first();
                                  
        $parent_files = FileManager::where("parent_id",$filemanager->id)
                                   ->where("company_id",company_id());                    


        $file_name = $filemanager->file;
        $file = storage_path().'/uploads/file_manager/'.$file_name;                                
                                                        
        if(File::exists($file))
        {                       
            File::delete($file);                                    
        }

        $parent_files->delete();
        $filemanager->delete();
        
        
        return redirect()->route('file_manager.index')->with('success',_lang('Deleted Sucessfully'));
    }
    
    
    private function is_duplicate_file($name, $parent_id, $ignore_id=''){
        $file = FileManager::where("name",$name)
                           ->where("parent_id",$parent_id)  
                           ->where("is_dir","no")   
                           ->where("id","!=",$ignore_id)    
                           ->where("company_id",company_id());  
        if( $file->exists() ){
            return true;
        }
        return false;
    }
    
    private function is_duplicate_folder($name, $parent_id, $ignore_id=''){
        $file = FileManager::where("name",$name)
                           ->where("parent_id",$parent_id)  
                           ->where("is_dir","yes")
                           ->where("id","!=",$ignore_id)    
                           ->where("company_id",company_id());  
        if( $file->exists() ){
            return true;
        }
        return false;
    }


    public function downloadFile($id){
        $users_has_access = \App\User::select('id')->where("user_type","staff")
                     ->Where("company_ids",'like', '%|'.company_id().'|%')
                     ->orderBy("id","desc")->get()->keyBy('id')->keys()->toArray();

        $filemanager = FileManager::where("id",$id)->whereIn("company_id",[0, company_id()])
            ->where(function($query) use ($users_has_access){
                $query->where("created_by", Auth::user()->id);
                if(in_array(Auth::user()->id, $users_has_access)) {
                    $query->orWhereRaw("1 = 1");
                } elseif(count($users_has_access)) {
                    $query->orWhereIn("created_by", $users_has_access);
                }
                $query->orWhere("created_by", "=", 0);
             })->first();

        if($filemanager)
        {
            $file_name = $filemanager->file;
            $file = storage_path().'/uploads/file_manager/'.$file_name;
            
            if(File::exists($file))
            {
                return response()->download($file);
            }
        }
        else
        {
            return redirect()->back();
        }
    }
    
    public function downloadAll($parent_id){
        $users_has_access = \App\User::select('id')->where("user_type","staff")
                     ->Where("company_ids",'like', '%|'.company_id().'|%')
                     ->orderBy("id","desc")->get()->keyBy('id')->keys()->toArray();
            
        $all_filemanagers = FileManager::whereIn("company_id",[0, company_id()])
            ->where(function($query) use ($users_has_access){
                $query->where("created_by", Auth::user()->id);
                if(in_array(Auth::user()->id, $users_has_access)) {
                    $query->orWhereRaw("1 = 1");
                } elseif(count($users_has_access)) {
                    $query->orWhereIn("created_by", $users_has_access);
                }
                $query->orWhere("created_by", "=", 0);
             })->get()->keyBy('id')->toArray();
        function prepareCategories(array $categories)
        {
            $result = [
                'all_categories' => [],
                'parent_categories' => []
            ];
            foreach ($categories as $category) {
                $result['all_categories'][$category['id']] = $category;
                $result['parent_categories'][$category['parent_id']][] = $category['id'];
                $result['valid'][$category['id']] = $category['is_dir']=="yes" ? 0 : 1;
            }
            return $result;
        }
        $prepared_categories = prepareCategories($all_filemanagers);
        
        function buildCategories($categories, $parentId = null, $ret=[]) { // buildCategories($cat1, $parent_id)
            if (!isset($categories['parent_categories'][$parentId])) {
                return;
            }

            foreach ($categories['parent_categories'][$parentId] as $cat_id) {
                if (isset($categories['parent_categories'][$cat_id])) {
                    $ret =  buildCategories($categories, $cat_id, $ret);
                } else {
                    if($categories['valid'][$cat_id])
                        $ret[]  = $categories['all_categories'][$cat_id]['file'];
                }
            }

            return $ret;
        }
        
        $sub_downloads = buildCategories($prepared_categories, $parent_id);

        if(!is_array($sub_downloads) || !count($sub_downloads))
            return _lang("There are no files to download");

        $zip_file = storage_path(date('YmdHis').company_id().".zip");
        
        foreach ($sub_downloads as $file_name) {
            $file = storage_path().'/uploads/file_manager/'.$file_name;
            if(File::exists($file)) {
                if(!isset($zip)) {
                    $zip = new \ZipArchive();
                    $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                }
                $zip->addFile($file, $file_name);
            }
        }
        if(isset($zip)) {
            $zip->close();
            return response()->download($zip_file)->deleteFileAfterSend(true);
        }
        return _lang("There are no files to download");
    }
    
    public function giveDownloadPermissionToStaff() {
        if(\Auth::user()->user_type != "staff")
            return;
        // \DB::beginTransaction();
        $staff_user = \Auth::user();
        
        $conta2_filemanager_client_ids = AccessControl::where(['permission'=>'file_manager.index', 'user_id'=>$staff_user->id])->where(['staff_company_id' => company_id()])->get()->pluck('staff_company_id')->toArray();
        if($conta2_filemanager_client_ids) {
            $conta2_filemanager_client_ids = AccessControl::where(['permission'=>'file_manager.download', 'user_id'=>$staff_user->id])->where(['staff_company_id' => company_id()])->get()->pluck('staff_company_id')->toArray();
            if(!$conta2_filemanager_client_ids) {
                $permission = new AccessControl;
                $permission->user_id = $staff_user->id;
                $permission->permission = 'file_manager.download';
                $permission->staff_company_id = company_id();
                $permission->save();
            }
        }
    }

    public function previewFile($id){
        
        $users_has_access = \App\User::select('id')->where("user_type","staff")
                     ->Where("company_ids",'like', '%|'.company_id().'|%')
                     ->orderBy("id","desc")->get()->keyBy('id')->keys()->toArray();

        $filemanager = FileManager::where("id",$id)->whereIn("company_id",[0, company_id()])
            ->where(function($query) use ($users_has_access){
                $query->where("created_by", Auth::user()->id);
                if(in_array(Auth::user()->id, $users_has_access)) {
                    $query->orWhereRaw("1 = 1");
                } elseif(count($users_has_access)) {
                    $query->orWhereIn("created_by", $users_has_access);
                }
                $query->orWhere("created_by", "=", 0);
             })->first();

        if($filemanager)
        {
            $file_name = $filemanager->file;
            $file = storage_path().'/uploads/file_manager/'.$file_name;
            

            if(File::exists($file))
            {
                return response()->file($file);
            }
        }
        else
        {
            return redirect()->back();
        }
    }
    
}
