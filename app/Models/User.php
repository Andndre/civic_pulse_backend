<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'address', 'avatar', 'date_of_birth', 'gender', 'parent_name', 'parent_phone', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date:Y-m-d',
        ];
    }

    /**
     * The classes this student belongs to.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student', 'student_id', 'class_id')
            ->withPivot('grade')
            ->withTimestamps();
    }

    /**
     * The classes this teacher manages as homeroom teacher.
     */
    public function teachingClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    /**
     * The activity logs submitted by this student.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'student_id');
    }

    /**
     * The activity logs reviewed by this teacher.
     */
    public function reviewedActivities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'reviewed_by');
    }

    /**
     * The test responses (scores) of this student.
     */
    public function testResponses(): HasMany
    {
        return $this->hasMany(TestResponse::class, 'student_id');
    }

    /**
     * The anecdotal notes written about this student.
     */
    public function anecdotalNotes(): HasMany
    {
        return $this->hasMany(AnecdotalNote::class, 'student_id');
    }

    /**
     * The anecdotal notes written by this teacher.
     */
    public function writtenNotes(): HasMany
    {
        return $this->hasMany(AnecdotalNote::class, 'teacher_id');
    }
}
