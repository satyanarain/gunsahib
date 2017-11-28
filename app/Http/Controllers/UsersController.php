<?php
namespace App\Http\Controllers;

use Gate;
use Carbon;
use Datatables;
use Notifynder;
use DB;
use Excel;
use Schema;
use Response;
use App\Models\User;
use App\Models\Travel;
use App\Models\TravelDocument;
use App\Models\Country;
use App\Models\Tasks;
use App\Models\Vendor;
use App\Models\Companie;
use App\Http\Requests;
use App\Models\Client;
use App\Models\Associate;
use App\Models\Role;
use App\Models\GroupCompany;
use App\Models\CountryCallingCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PHPZen\LaravelRbac\Traits\Rbac;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserBankDetailRequest;
use App\Http\Requests\Travel\StoreTravelRequest;
use App\Http\Requests\Travel\UpdateTravelRequest;
use App\Repositories\User\UserRepositoryContract;
use App\Repositories\Role\RoleRepositoryContract;
use App\Repositories\Department\DepartmentRepositoryContract;
use App\Repositories\Setting\SettingRepositoryContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{
    protected $users;
    protected $roles;
    protected $departments;
    protected $settings;

    public function __construct(
        UserRepositoryContract $users,
        RoleRepositoryContract $roles,
        DepartmentRepositoryContract $departments,
        SettingRepositoryContract $settings
    ) {
        $this->users = $users;
        $this->roles = $roles;
        $this->departments = $departments;
        $this->settings = $settings;
        $this->middleware('user.create', ['only' => ['create']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $profilemenu = "profilemenu";
        $profilemenuusers = "profilemenuusers";

        return view('users.index', compact('profilemenu', 'profilemenuusers'));
    }

    public function anyData()
    {
        $canUpdateUser = auth()->user()->can('update-user');
        $users = DB::table('role_user')
        ->select(['users.id', 'users.name', 'users.middle_name', 'users.last_name', 'users.email', 'users.contact_number', 'users.calling_code_contact', 'users.status', 'roles.display_name'])
        ->join('roles', 'role_user.role_id', '=', 'roles.id')
        ->join('users', 'role_user.user_id', '=', 'users.id')
        ->where('group_company_id', 'like', '%'.session('companyId').'%')
        ->get();

        //echo json_encode($users);exit();
        return Datatables::of($users)
        ->addColumn('namelink', function ($users) {
                return $users->name;
        })

        ->add_column('edit', '
                <a style="background-color:#f7831a" href="{{ route(\'users.edit\', $id) }}" class="btn btn-success" ><span class="glyphicon glyphicon-pencil"></span> Edit</a>')
        ->add_column('view', function ($users) {
                return '<a style="background-color:#3A485C" class="btn btn-success" href="users/'.$users->id.'"><span class="glyphicon glyphicon-search"></span>View</a>';
        })
        ->add_column('contact', function ($users) {
                return '('.$users->calling_code_contact.') '.$users->contact_number;
        })
        ->add_column('status', function($users){ if ($users->status == 0) return 'Pending Activation'; elseif ($users->status == 1) return 'Active'; elseif ($users->status == 2) return 'In-active';})
        ->make(true);
    }

    public function getCompaniesUserHasAccessIn(){
        $user = User::findOrFail(Auth::id());
        $company_ids = $user->group_company_id;
        $company_ids = explode(',', $company_ids);

        $companies = GroupCompany::whereIn('id', $company_ids)->get();

        return $companies->toJson();
    }

    public function changeProfileImage(Request $request){
        $data = $request->image; 

        list($type, $data) = explode(';', $data);

        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        $file_name = str_random(40).'.jpeg';
        $folder_path = public_path().'/images/Media/'.$file_name;
        file_put_contents($folder_path, $data);

        $user = User::find($request->id);

        $user->image_path = $file_name;
        $user->save();

       return response()->json([
            'status' => 'success',
            'message' => 'Profile picture changed successfully!'
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $user = User::findOrFail(Auth::id());
        $codes = DB::table('countries_calling_code')->orderBy('country_name', 'desc')->pluck(DB::raw('CONCAT(country_name, " (", calling_code, ") ") AS calling_code_text'), 'calling_code');
        foreach ($codes as $key => $value) {
            $mod[$value] = $key; 
        }
        ksort($mod);
        foreach ($mod as $key => $value) {
            $newmod[$value] = $key; 
        }
        //echo json_encode($newmod);exit();
        if($user->hasRole('group_admin')){
            $roles = Role::orderBy('display_name', 'asc')->pluck('display_name', 'id');
        }else if($user->hasRole('administrator')){
            $roles = Role::orderBy('display_name', 'asc')->where('id', '<>', 9)
                        ->pluck('display_name', 'id');
        }
        return view('users.create')
                ->withRoles($roles)
                ->withDepartments($this->departments->listAllDepartments())
                ->withCountries(Country::orderBy('country_name', 'asc')->pluck('country_name', 'id'))
                //->withClients(Client::where(['company_id', '=', session('companyId')])->orderBy('client_name', 'asc')->pluck('client_name', 'id'))
                  ->withClients(Client::where([['company_id', '=', session('companyId')]])->orderBy('client_name', 'asc')->pluck('client_name', 'id'))
                ->withAssociates(Associate::orderBy('name', 'asc')->pluck('name', 'id'))
                ->withGroupCompanies(GroupCompany::orderBy('name', 'asc')->where('id', '<>', 99)->pluck('name', 'id'))
                ->withTelephoneCodes($newmod);
        
    }

    /**
     * Store a newly created resource in storage.
     * @param User $user
     * @return Response
     */
    public function store(StoreUserRequest $userRequest)
    {
        $getInsertedId = $this->users->create($userRequest);
        // exit();
        return redirect()->route('users.index');         
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
   public function show($id)
   {
        $userprofile="userprofile";

        $costcenters=DB::table('users')
        ->leftjoin('costcenters','costcenters.user_id','=','users.id')
        ->leftjoin('associates','associates.id','=','users.associate_id')
        ->join('services','services.id','=','costcenters.service_id')
        ->join('subservices','subservices.id','=','costcenters.subservice_id')
        ->leftjoin('nestedservices','nestedservices.id','=','costcenters.recordaltype_id')
        ->leftjoin('trademarktype','trademarktype.id','=','costcenters.trademarktype_id')
        ->leftjoin('trademarksubtype','trademarksubtype.id','=','costcenters.trademarksubtype_id')
        ->join('countries','countries.id','=','costcenters.country')
        ->select('services.servicename','subservices.subservice_name','nestedservices.nestedservicename','trademarktype.type_name','trademarksubtype.sub_type_name','associates.currency','countries.country_name')
        ->where('costcenters.user_id',$id)
        ->get();
    
         
        //return view('users.cost_center',compact('costcenters'));

        $travel = Travel::join('countries', 'travel.country', '=', 'countries.id')
        ->select(['travel.*', 'countries.country_name'])
        ->where('user_id', '=', $id)->get();
        //echo json_encode($travel);exit();
          $country = User::join('countries','countries.id','=','users.country')
         ->select('country_name')->where('users.id',$id)->first();
         
         $groupcompany = User::join('group_companies','group_companies.id','=','users.group_company_id')
        ->select('group_companies.name as groupcompany_name')->where('users.id',$id)->first();

        $usertype = User::join('roles','roles.id','=','users.user_type')
        ->select('roles.display_name')->where('users.id',$id)->first();

        $clients = User::leftjoin('clients','clients.id','=','users.client_id')
        ->select('*')->where('users.id',$id)->first();
        $client = User::leftjoin('clients','clients.id','=','users.client_id')
        ->select('*')->where('users.id',$id)->first();
        $clientname = $clients->client_name;
 
        $associate = User::join('associates','associates.id','=','users.associate_id')
        ->select('associates.name as associate_name')->where('users.id',$id)->first();

        return view('users.show',compact('userprofile'))
        ->withUser($this->users->find($id))
        ->withCompanyname($this->settings->getCompanyName())
        ->withTravels($travel)
        ->withCostcenters($costcenters)
         ->withCountries($country)
        ->withGroupcompanies($groupcompany)
        ->withUsertypes($usertype)        

        ->withAssociates($associate)
        ->withClientname($clientname)
        ->withClient($client);
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

        $countries = Country::pluck('country_name', 'id');
       // $clients = Client::where('company_id', '=', session('companyId'))->orderBy('client_name', 'asc')->pluck('client_name', 'id');
        $clients = Client::where([['company_id', '=', session('companyId')]])->orderBy('client_name', 'asc')->pluck('client_name', 'id');
        $associates = Associate::pluck('name', 'id');
        $codes = DB::table('countries_calling_code')->orderBy('country_name', 'desc')->pluck(DB::raw('CONCAT(country_name, " (", calling_code, ") ") AS calling_code_text'), 'calling_code');
        foreach ($codes as $key => $value) {
            $mod[$value] = $key; 
        }
        ksort($mod);
        foreach ($mod as $key => $value) {
            $newmod[$value] = $key; 
        }

       $bankid = DB::table('users')->where('id','=',$id)->first();
      
      $bank_country_id= $bankid->bank_country;
    // echo json_encode($bank_country_id);exit();
        return view('users.edit',compact('bank_country_id',$bank_country_id))
        ->withUser($this->users->find($id))
        ->withRoles($this->roles->listAllRoles())
        ->withClients($clients)

        ->withAssociates($associates)
        ->withCountries($countries)
        ->withGroupCompanies(GroupCompany::where('id', '<>', 99)->pluck('name', 'id'))
        ->withTelephoneCodes($newmod);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, UpdateUserRequest $request)
    {
        $this->users->update($id, $request);
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->users->destroy($id);
        
        return redirect()->route('users.index');
    }

    public function createdPassword($token){
        return view('users.activate', ['token' => $token]);
    }






    public function setPassword(Request $request){
        //var_dump($request);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
        ]);

        $check_if_email_is_registered = User::where([
            ['email', '=', $request->email]
        ])->first();

        $validator->after(function($validator) use ($check_if_email_is_registered)
        {
            if (!$check_if_email_is_registered)
            {
                $validator->errors()->add('email_not_registered', 'This email is not registered!');
            }
        });


        if ($validator->fails()) {
            return redirect(route('password.create', $request->token))
                ->withInput()
                ->withErrors($validator);
        }

        $token = User::where([
            ['email', '=', $request->email],
            ['set_password_token','=', $request->token]
           // ['created_at','>',Carbon::now()->subHours(24)]
        ])->first();

        if($token){
            $user = User::find($token->id);
            //set password
            $user->password = bcrypt($request->password);
            //change status
            $user->status = 1;
            $user->save();

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                //echo json_encode($token);exit();
                return redirect('dashboard');
            }
        }else{
            return "Link has been expired. Please contact to admin.";
        }
    }





    public function checkIfEmailIsRegistered(Request $request){
        $check_if_email_is_registered = User::where([
            ['email', '=', $request->email]
        ])->first();


        return $check_if_email_is_registered;
    }

    public function showTravelModal($id, Request $request){
        $userprofile="userprofile";
        return view('modals.create_travel',compact('userprofile'))
        ->withUser(User::findOrFail($id))
        ->withCountries(Country::pluck('country_name', 'id'));
    }

    public function showEditTravelModal($travel_id){
          $userprofile="userprofile";
        $travel = Travel::findOrFail($travel_id);
        return view('modals.edit_travel',compact('userprofile'))
        ->withTravel($travel)
        ->withUser(User::findOrFail(Auth::id()))
        ->withCountries(Country::pluck('country_name', 'id'))
        ->withDocuments(TravelDocument::where('travel_id', '=', $travel_id)->get());
    }

    public function saveTravelDetail($id, StoreTravelRequest $request)
    {    	
        $input = $request->all();
        $input['user_id'] = $id;   
        
        $input['visa_from'] = date('Y-m-d', strtotime($request->visa_from));
        $input['visa_to'] = date('Y-m-d', strtotime($request->visa_to));
        $input['passport_expires_on'] = date('Y-m-d', strtotime($request->passport_expires_on));
        $input['booking_from'] = date('Y-m-d', strtotime($request->booking_from));
        $input['booking_to'] = date('Y-m-d', strtotime($request->booking_to));

        $input['travel_from_date'] = date('Y-m-d', strtotime($request->travel_from_date));
        $input['travel_to_date'] = date('Y-m-d', strtotime($request->travel_to_date));



        $document_types = $request->document_types;
        //echo json_encode($document_types);exit();
        $travel = Travel::create($input);

        if ($request->hasFile('travel_documents')) {

            $documents = $request->file('travel_documents');
            foreach ($documents as $key => $value) {
                $filename = str_random(40).'.'.$value->extension();
                if (!is_dir(public_path(). '/images/Travel')) {
                    mkdir(public_path(). '/images/Travel', 0777, true);
                }
                $path = public_path(). '/images/Travel';
                //echo $path; exit();
                //$file =  $value->file('travel_document');
                $value->move($path, $filename);
                $input_travel['document_path'] = $filename;
                $input_travel['travel_id'] = $travel->id;
                $input_travel['document_type'] = $document_types[$key];

                TravelDocument::create($input_travel);
            }
            
        }  

        //send notification to admin when user travel profile is created
        Notifynder::category('user.travelprofilecreated')
                ->from(auth()->id())
                ->to(1)
                ->url(url('users/'.auth()->id().'/showtravelprofile/'.$travel->id))
                ->expire(Carbon::now()->addDays(30))
                ->send();

        Session()->flash('flash_message', 'Travel profile successfully added.');
        return redirect(route('users.show', $id));
    }


    public function updateTravelDetail($travel_id, UpdateTravelRequest $request){


        $input = $request->all();
        $id = Auth::user()->id;
        $input['user_id'] = $id;
        $travel = Travel::findOrFail($travel_id);
        $input['visa_from'] = date('Y-m-d', strtotime($request->visa_from));
        $input['visa_to'] = date('Y-m-d', strtotime($request->visa_to));
        $input['passport_expires_on'] = date('Y-m-d', strtotime($request->passport_expires_on));
        $input['booking_from'] = date('Y-m-d', strtotime($request->booking_from));
        $input['booking_to'] = date('Y-m-d', strtotime($request->booking_to));

        $input['travel_from_date'] = date('Y-m-d', strtotime($request->travel_from_date));
        $input['travel_to_date'] = date('Y-m-d', strtotime($request->travel_to_date));
        
        $document_types = $request->document_types;
        
        /* foreach ($document_types as $type){
        	$travel_documents = TravelDocument::where([['travel_id', '=', $travel->id], ['document_type', '=', $type]])->delete();
        } */


        if ($request->hasFile('travel_documents')) {

            $documents = $request->file('travel_documents');
            foreach ($documents as $key => $value) {
                $filename = str_random(40).'.'.$value->extension();
                if (!is_dir(public_path(). '/images/Travel')) {
                    mkdir(public_path(). '/images/Travel', 0777, true);
                }
                $path = public_path(). '/images/Travel';
                //echo $path; exit();
                //$file =  $value->file('travel_document');
                $value->move($path, $filename);
                $input_travel['document_path'] = $filename;
                $input_travel['travel_id'] = $travel->id;
                $input_travel['document_type'] = $document_types[$key];

                TravelDocument::create($input_travel);
            }
            
        }       
        
        $travel->fill($input)->save();

        //send notification to admin when user travel profile is updated
        Notifynder::category('user.travelprofileupdated')
                ->from(auth()->id())
                ->to(1)
                ->url(url('users/'.auth()->id().'/showtravelprofile/'.$travel->id))
                ->expire(Carbon::now()->addDays(30))
                ->send();
        
        Session()->flash('flash_message', 'Travel profile successfully updated');
        return redirect(route('users.show', $id));
    }


    public function showTravelDetail($user_id, $travel_id){
        $travel = Travel::join('countries', 'countries.id', '=', 'travel.country')
                    ->select('travel.*', 'countries.country_name as country_travelled')
                    ->where('travel.id', '=', $travel_id)
                    ->first();
        $user = User::findOrFail($user_id);
        $travel_document = TravelDocument::where('travel_id', '=', $travel_id)->get();
        return view('modals.traveldetail')
                ->withTravel($travel)
                ->withUser($user)
                ->withTravelDocuments($travel_document);
    }

    public function editBankDetail($userId){
        $countries = Country::pluck('country_name', 'id');
        return view('modals.edit_bank_detail')
                ->withUser(User::findOrFail(Auth::id()))
                ->withCountries($countries);
    }

    public function updateBankDetail($userId, UpdateUserBankDetailRequest $request){
        $user = User::findOrFail(Auth::id());

        $user->beneficiary_name = $request->beneficiary_name;
        $user->bank_name        = $request->bank_name;
        $user->branch_name      = $request->branch_name;
        $user->branch_address   = $request->branch_address;
        $user->account_type     = $request->account_type;
        $user->account_number   = $request->account_number;
        $user->swift_code       = $request->swift_code;
        $user->ifsc_code        = $request->ifsc_code;
        $user->beneficiary_address = $request->beneficiary_address;
        $user->beneficiary_country = $request->beneficiary_country;
        $user->beneficiary_pin_code = $request->beneficiary_pin_code;
        $user->bank_country = $request->bank_country;
        $user->bank_pin_code = $request->bank_pin_code;
        $user->routing_code = $request->routing_code;

        $user->save();

        Session()->flash('flash_message', 'Bank detail successfully updated');
        return redirect(route('users.show', Auth::id()));
    }
    
    public function changeName(Request $request){
        $user_id = $request->user_id;
        if($user_id == Auth::id()){
            $user = User::findOrFail($request->user_id);
            $user->name = $request->newName;
            $user->save();
            $arr = array('status' => 'Ok', 'newName' => $user->name);
        }else{
            $arr = array('status' => 'Error');
        }

        echo json_encode($arr);
    }

    public function changeContact(Request $request){
        $user_id = $request->user_id;
        if($user_id == Auth::id()){
            $user = User::findOrFail($request->user_id);
            $user->contact_number = $request->newContact;
            $user->save();
            $arr = array('status' => 'Ok', 'newContact' => $user->contact_number);
        }else{
            $arr = array('status' => 'Error');
        }

        echo json_encode($arr);
    }

    public function changeAddress(Request $request){
        $user_id = $request->user_id;
        if($user_id == Auth::id()){
            $user = User::findOrFail($request->user_id);
            $user->address = $request->newAddress;
            $user->save();
            $arr = array('status' => 'Ok', 'newAddress' => $user->address);
        }else{
            $arr = array('status' => 'Error');
        }

        echo json_encode($arr);
    }

    public function deleteTravelDetail($user_id, $travel_id){

        $travel = Travel::findorFail($travel_id);
        $travel->delete();
        Session()->flash('flash_message', 'Travel successfully deleted');        
        
        return redirect()->route('users.show', $user_id);
    }


    public function printTravelDetails($user_id){
        $travels = Travel::join('countries', 'countries.id', '=', 'travel.country')
            ->select('travel.*', 'countries.country_name')
            ->where('user_id', '=', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=travel_details.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        //$reviews = Reviews::getReviewExport($this->hw->healthwatchID)->get();
        $columns = array('Country Travelled', 'Visa Number', 'Visa Valid From', 'Visa Valid Upto', 'Visa Type', 'Travel From Date', 'Travel To Date', 'Travelling Summary', 'Hotel Name', 'Hotel Address', 'Hotel Check-In', 'Hotel Check-Out', 'Passport Number', 'Passport Expires On', 'Note Section', 'Created At');

        $callback = function() use ($travels, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($travels as $travel) {
                fputcsv($file, array($travel->country_name, $travel->visa_number, $travel->visa_from, $travel->visa_to, $travel->visa_type, $travel->travel_from_date, $travel->travel_to_date, $travel->summary, $travel->hotel_name, $travel->hotel_address, $travel->booking_from, $travel->booking_to, $travel->passport_number, $travel->passport_expires_on, $travel->note, $travel->created_at));
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);

    }
    
    
}
