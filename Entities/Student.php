<?php

namespace Modules\Student\Entities;

use App\Models\Mark;
use App\Models\User;
use App\Models\State;
use App\Models\Branch;
use App\Models\SClass;
use App\Models\Section;
use App\Models\Session;
use App\Models\Guardian;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LocalGovernment;
use Spatie\MediaLibrary\HasMedia;
use Modules\Admission\Entities\Enrol;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model implements HasMedia
{
    use HasFactory,  InteractsWithMedia;

    protected $fillable = ['s_class_id', 'session_id', 'section_id', 'branch_id'];

    public function enrol(){
        return $this->belongsTo(Enrol::class);
    }


    public function branch(){
        return $this->belongsTo(Branch::class);
    }

    public function guardian(){
        return $this->belongsTo(Guardian::class);
    }

    public function dept(){
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function class(){
        return $this->belongsTo(SClass::class, 's_class_id');
    }


    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function lga(){
        return $this->belongsTo(LocalGovernment::class, 'local_government_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }


    public function state(){
        return $this->belongsTo(State::class);
    }

    public function session(){
        return $this->belongsTo(Session::class);
    }



    public function getEnrolDateAttribute($value){
        return date('M, d Y', strtotime($value));
    }

    public function getCreatedAtAttribute($value){
        return date('M, d Y', strtotime($value));
    }



    public function attendances(){
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function getSumOfMark($branch, $class, $section, $student){
        $mark = Mark::where([
            'branch_id'=>$branch,
            'section_id'=>$section,
            's_class_id'=>$class,
            'session_id'=>activeSession()->id,
            'student_id'=>$student,
            ])->sum('mark');

        return $mark;
    }


}
