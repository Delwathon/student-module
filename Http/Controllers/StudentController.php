<?php

namespace Modules\Student\Http\Controllers;

use App\Models\Term;
use App\Models\User;
use App\Models\State;
use App\Models\Branch;
use App\Models\SClass;
use App\Models\Section;
use App\Models\Session;
use App\Models\Guardian;
use App\Models\Promotion;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Student\Entities\Student;
use Illuminate\Contracts\Support\Renderable;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if($request->get('branch') && $request->get('class') && $request->get('section')){
            $request->validate([
                'branch'=>'required|numeric'
            ]);
            return redirect()->route('student.filter', [$request->branch, $request->class, $request->section]);
        }
        $branches = Branch::get();
        // return $branches;
        return view('student.index', compact(['branches']));
    }

    public function studentFilter($branch, $class, $section){
        $branch=Branch::find($branch);
        $section = Section::find($section);
        $class = SClass::find($class);
        $students = Student::with(['branch', 'enrol','dept','user','state','lga','class', 'section','guardian'])
        ->where(['branch_id'=>$branch->id,
        'section_id'=>$section->id,
        's_class_id'=>$class->id])->get();
        // return $students;
        $branches = Branch::get();
        $classes = SClass::whereHas('branches', function ($query) use ($branch) {
            $query->where('branch_id',$branch->id);
        })->get();
        $sections = Section::whereHas('classes', function ($query) use ($class) {
            $query->where('s_class_id',$class->id);
        })->get();
        return view('student.index', compact(['branches','classes','sections', 'students', 'section', 'branch', 'class']));

    }

    public function inactive(Request $request)
    {
        if($request->get('session') && $request->get('status')){
            $request->validate([
                'session'=>'required|numeric',
                'status'=>'required|string',
            ]);
            return redirect()->route('inactive.list', [$request->session, $request->status]);
        }
        $sessions = Session::get();
        $all_status = ['Suspended' => 'suspension',
                        'Rusticated' => 'expulsion'];
        return view('student.inactive', compact(['sessions', 'all_status']));
    }

    public function inactiveStudent($session, $status)
    {
        $students = Student::with(['branch', 'enrol','dept','user','state','lga','class', 'section','guardian'])
        ->where(['session_id'=>$session, 'status'=>$status])->get();
        // return $students
        $sessions = Session::get();
        $all_status = ['Suspended' => 'suspension',
                        'Rusticated' => 'expulsion'];
        $session = Session::find($session);

        return view('student.inactive', compact(['session', 'status', 'sessions', 'all_status', 'students']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $student = Student::with(['branch','enrol', 'dept','session','user','state','lga','class', 'section','guardian'])
        ->where('id', $id)->first();
        $sessions = Session::get();


        $branches = Branch::get();
        $section = Section::whereHas('classes', function ($query) use ($student) {
            $query->where('s_class_id',$student->class->id);
        })->get();
        $class = SClass::whereHas('branches', function ($query) use ($student) {
            $query->where('branch_id',$student->branch->id);
        })->get();
        // return $class;
        $states = State::get();
        $guardian = Guardian::where('id', $student->guardian->id)->get();
        $dept = Department::where('branch_id',$student->branch->id)->get();

        $promotions = Promotion::with(['fromclass','branch', 'toclass', 'fromsection', 'tosection', 'fromsession', 'tosession'])->where(['student_id'=> $student->id])->get();

        $terms = Term::orderBy('name', 'asc')->where('branch_id', $student->branch->id)->get();


        // return $promotions;

        return view('student.profile', compact(['student','promotions','terms','dept','guardian', 'states','sessions', 'branches', 'class', 'section']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //

        $request->validate([
            'section'=>'required|numeric',
            'regno'=>'required|string',
            'rollno'=>'required|numeric',
            'department'=>'required|numeric',
            'class'=>'required|numeric',
            'state'=>'required|numeric',
            'lga'=>'required|numeric',
            'firstname'=>'required|string',
            'lastname'=>'required|string',
            'middlename'=>'sometimes|nullable|string',
            'gender'=>'required|string',
            'dob'=>'required|string',
            'religion'=>'required|string',
            'bloodgroup'=>'required|string',
            'genotype'=>'required|string',
            'mothertongue'=>'required|string',
            'email'=>'required|string',
            'mobile'=>'sometimes|nullable|string',
            'city'=>'required|string',
            'password'=>'sometimes|required|confirmed',
            'address'=>'required|string',
        ]);

        $student = Student::with(['branch', 'enrol','dept','user','state','lga','class', 'section','guardian'])
        ->where('id', $id)->first();
        $user = User::where('id', $student->user_id)->first();
        $user->email = $request->email;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->middlename = $request->middlename;
        if ($request->password){
            $user->password = Hash::make($request->password);
        }


        if ($request->file("avatar")) {
            // $user->delete($user->id);
            $user->clearMediaCollection('avatar');
            $fileName = str_replace(" ", "_", $request->firstname . " " . $request->lastname);
            $user->addMediaFromRequest('avatar')->usingFileName($fileName)->toMediaCollection("avatar");
        }

        $user->save();


        $student->guardian_id = $request->guardian_id;
        $student->mobile = $request->mobile;
        $student->blood_group = $request->bloodgroup;
        $student->session_id = $request->session;
        $student->genotype = $request->genotype;
        $student->mothertongue = $request->mothertongue;
        $student->mobile = $request->mobile;
        $student->city = $request->city;
        // $student->enrol_date = $user->enrol_date;
        $student->dob = date('Y-m-d',strtotime($request->dob));
        $student->state_id = $request->state;
        $student->local_government_id = $request->lga;
        $student->address = $request->address;
        $student->gender = $request->gender;
        $student->religion = $request->religion;
        $student->department_id = $request->department;
        $student->regno =   $request->regno;//$request->regno;
        $student->rollno = $request->rollno;  //$request->section;
        $student->section_id = $request->section;
        $student->s_class_id = $request->class;
        $student->update();
        return redirect()->back()->with('success', 'Student Basic details updated successfully');



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
