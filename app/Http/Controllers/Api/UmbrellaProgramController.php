<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\UmbrellaProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UmbrellaProgramController extends Controller
{
    public function getUmbrella(Request $request)
    {
        $checkAccount =  auth('sanctum')->user();

        $getUmbrella = UmbrellaProgram::where('faculty_id', $checkAccount->students->teams->facultys->id)->get();
        foreach ($getUmbrella as $item) {
            $result[] = [
                'subject' => $item->subjects->name,
                'code' => $item->subjects->code,
                'credits' => $item->subjects->credits,
                'theory_lesson' => $item->subjects->theory_lesson,
                'practice_lesson' => $item->subjects->practice_lesson,
                'term' => $item->term,
                'type_subject' => $item->subjects->type_subjects->name,
            ];
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'umbrella_programs' => $result
        ];

        return response($response, 201);
    }

    public function get_umbrella_program_by_faculty($faculty_id)
    {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $umbrellaPrograms = UmbrellaProgram::get();
        if ($faculty_id != 0)
            $umbrellaPrograms = UmbrellaProgram::where('faculty_id', $faculty_id)->get();

        foreach ($umbrellaPrograms as $item) {
            $result[] = [
                'subject' => $item->subjects->name,
                'id' => $item->subjects->id,
                'code' => $item->subjects->code,
                'credits' => $item->subjects->credits,
                'theory_lesson' => $item->subjects->theory_lesson,
                'practice_lesson' => $item->subjects->practice_lesson,
                'term' => $item->term,
                'type_subject' => $item->subjects->type_subjects->name,
                'class_subject' => $item->subjects->class_subjects,
            ];
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'umbrella_programs' => $result,
        ];

        return response($response, 201);
    }

    public function assignment_create(Request $request)
    {

        $faculty = Faculty::find($request->faculty_id);
        if (!$faculty) return response(["status" => 403, 'success' => 'danger', 'message' => 'Không tìm thấy khoa'], 403);

        $get_subjects = Subject::whereIn('id', $request->class_subjects)->get();

        foreach ($get_subjects as $subject) {
            $subjects[] = [
                'id' => $subject->id,
                'name' => $subject->name,
                'credits' => $subject->credits,
                'term' => UmbrellaProgram::where('faculty_id', $faculty->id)->where('subject_id', $subject->id)->first()->term,
                'theory_lesson' => $subject->theory_lesson,
                'practice_lesson' => $subject->practice_lesson,
                'total_lesson' => $subject->theory_lesson + $subject->practice_lesson,
                'subject_group' => $subject->subject_group->ac_name,
                'quantity_students' => $subject->quantity_students,
            ];
        }

        $get_teachers = Teacher::where('faculty_id', $faculty->id)->get();

        foreach ($get_teachers as $teacher) {
            $teachers[] = [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'busy' => $teacher->busy,
                'gio_chuan_giang_day' => $teacher->gio_chuan_giang_day,
            ];
        }


        // Tính giờ chuẩn cho từng học phần
        foreach ($subjects as $index => $subject) {

            $he_so_thuc_hanh = 1;
            $he_so_ly_thuyet = 1.1;

            // Tính hệ số lý thuyết
            switch ($subject['subject_group']) {
                case 'NN':
                    if ($subject['quantity_students'] <= 40)
                        $he_so_ly_thuyet = 1.1;
                    else if ($subject['quantity_students'] > 40 && $subject['quantity_students'] <= 60)
                        $he_so_ly_thuyet = 1.2;
                    else if ($subject['quantity_students'] > 60)
                        $he_so_ly_thuyet = 1.4;
                    break;
                case 'GDTC':
                    if ($subject['quantity_students'] <= 40)
                        $he_so_ly_thuyet = 1.1;
                    else if ($subject['quantity_students'] > 40)
                        $he_so_ly_thuyet = 1.2;
                    break;
                case 'GDQP':
                    if ($subject['quantity_students'] <= 40)
                        $he_so_ly_thuyet = 1.1;
                    else if ($subject['quantity_students'] > 40)
                        $he_so_ly_thuyet = 1.2;
                    break;
                default:
                    if ($subject['quantity_students'] <= 40)
                        $he_so_ly_thuyet = 1.1;
                    else if ($subject['quantity_students'] > 40 && $subject['quantity_students'] <= 100)
                        $he_so_ly_thuyet = 1.2;
                    else if ($subject['quantity_students'] > 100 && $subject['quantity_students'] <= 150)
                        $he_so_ly_thuyet = 1.4;
                    else if ($subject['quantity_students'] > 150)
                        $he_so_ly_thuyet = 1.5;
                    break;
            }

            $subjects[$index]['total_time'] = $he_so_thuc_hanh * $subject['practice_lesson'] + $he_so_ly_thuyet * $subject['theory_lesson'];
        }

        // 
        foreach ($teachers as $index_teacher => $teacher) {

            $gio_chuan = $teacher['gio_chuan_giang_day'] / 2;

            for ($i = 1; $i < 4; $i++) {
                $case = 0;
                switch ($i) {
                    case 1:
                        foreach($subjects as $index_subject => $subject) {
                            if ($subject['total_time'] > $gio_chuan - 10 && $subject['total_time'] < $gio_chuan + 10) {
                                $case = 1;
                                $teachers[$index_teacher]['subject_assignment'] = [$subject];
                                array_splice($subjects, $index_subject, 1);
                                break;
                            }
                        }
                        break;
                    case 2:
                        foreach ($subjects as $index_subject => $subject) {
                            for ($index = $index_subject + 1; $index < count($subjects); $index++) {
                                if ($subject['total_time'] + $subjects[$index]['total_time'] >  $gio_chuan - 20 && $subject['total_time'] + $subjects[$index]['total_time'] <  $gio_chuan + 20) {
                                    $case = 1;
                                    $teachers[$index_teacher]['subject_assignment'] = [$subjects[$index_subject], $subjects[$index]];
                                    array_splice($subjects, $index_subject, 1);
                                    array_splice($subjects, $index, 1);
                                    break;
                                }
                            }

                            if ($case === 1) break;
                        }
                        break;
                    case 3: 
                        foreach ($subjects as $loop1 => $subject) {
                            for ($loop2 = $loop1 + 1; $loop2 < count($subjects); $loop2++) {
                                for ($loop3 = $loop2 + 1; $loop3 < count($subjects); $loop3++) {
                                    if ($subject['total_time'] + $subjects[$loop2]['total_time'] + $subjects[$loop3]['total_time'] >  $gio_chuan - 30 && $subject['total_time'] + $subjects[$loop2]['total_time'] + $subjects[$loop3]['total_time'] <  $gio_chuan + 30) {
                                        $case = 1;
                                        $teachers[$index_teacher]['subject_assignment'] = [$subjects[$loop1], $subjects[$loop2], $subjects[$loop3]];
                                        array_splice($subjects, $loop1, 1);
                                        array_splice($subjects, $loop2, 1);
                                        array_splice($subjects, $loop3, 1);
                                        break;
                                    }
                                }
                                if ($case === 1) break;
                            }
                            if ($case === 1) break;
                        }
                    default:
                }

                if ($case === 1) break;
            }   

        }
        

        $response = [
            'status' => 201,
            'success' => 'success',
            'data' => $teachers,
            'conlai' => $subjects,
        ];

        return response($response, 201);
    }

    private function combinatoric($n, $k) {
        $cn  = 1; // n!
        $ck  = 1; // k!
        $cnk = 1; // (n-k)!
    
        for ($i = 2; $i <= $n; $i++) {
            $cn *= $i;
            if ($i == $k) $ck = $cn;
            if ($i == $n-$k) $cnk = $cn;
        }
    
        return $cn / ($ck * $cnk);
    }
}