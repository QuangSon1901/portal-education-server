<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TypeRoom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScheduleController extends Controller
{
    public function schedule_create(Request $request)
    {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $classSubjectSelected = $this->selectiveClassSubject($request->class_subjects, $request->subjects);

        $sortRandomOnRoomAready = $this->sortRandomOnRoom($request->rooms, $classSubjectSelected, $request->assignment, $request->teachers, $request->date);

        for ($index = 0; $index < count($request->rooms); $index++) {
            $result[] = [
                'id' => $request->rooms[$index]['id'],
                'code' => $request->rooms[$index]['code'],    
                'type_room' => $request->rooms[$index]['type_room'],    
                'quantity' => $request->rooms[$index]['quantity'],    
                'data' => $sortRandomOnRoomAready[$index],    
            ];
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'data' => $result,
        ];

        return response($response, 201);
    }

    private function selectiveClassSubject($class_subjects, $subjects)
    {
        $result = [];
        foreach ($class_subjects as $classSubjectItem) {
            $subject = Subject::find($classSubjectItem['subject_id']);

            $shiftOnWeek = $subject->credits >= 4 ? 2 : 1;

            $groupSubject = 0;
            foreach ($subjects as $subjectItem) {
                if ($subjectItem['id'] == $classSubjectItem['subject_id']) {
                    $groupSubject = $subjectItem['group'];
                    break;
                }
            }

            $typeRoom = '';
            if ($subject->practice_lesson > 0) $typeRoom = 'LAB';
            else $typeRoom = "LT";

            $result[] = [
                'id' => $classSubjectItem['id'],
                'subject_id' => $classSubjectItem['subject_id'],
                'credits' => $subject->credits,
                'shift_on_week' => $shiftOnWeek,
                'group' => $groupSubject,
                'type_room' => $typeRoom,
                'max_student' => ClassSubject::find($classSubjectItem['id'])->max_student,
            ];
        }

        return $result;
    }

    private function sortRandomOnRoom($rooms, $classSubject, $assignment, $teachers, $date_start)
    {

        $classSubjectsTemp = [...$classSubject];

        $result = [];
        for ($arrIndex = 0; $arrIndex < 2; $arrIndex++) {
            array_push($result, []);
            for ($rowIndex = 0; $rowIndex <= 1; $rowIndex++) {
                array_push($result[$arrIndex], []);
                for ($columnIndex = 0; $columnIndex <= 5; $columnIndex++) {
                    array_push($result[$arrIndex][$rowIndex], null);
                }
            }
        }

        for ($arrIndex = 0; $arrIndex < count($rooms); $arrIndex++) {
            $result[$arrIndex] = [];
            for ($rowIndex = 0; $rowIndex <= 1; $rowIndex++) {
                $result[$arrIndex][$rowIndex] = [];
                for ($columnIndex = 0; $columnIndex <= 5; $columnIndex++) {
                    $result[$arrIndex][$rowIndex][$columnIndex] = null;
                }
            }
        }

        for ($loop = 0; $loop < 100; $loop++) {
            for ($arrIndex = 0; $arrIndex < count($rooms); $arrIndex++) {
                $roomId = (int) $rooms[$arrIndex]['id'];
                $roomQuantity = (int) $rooms[$arrIndex]['quantity'];
                $typeRoomId = (int) $rooms[$arrIndex]['type_room'];
                $typeRoom = TypeRoom::find($typeRoomId);

                for ($rowIndex = 0; $rowIndex <= 1; $rowIndex++) {
                    for ($columnIndex = 0; $columnIndex <= 5; $columnIndex++) {

                        if (count($classSubjectsTemp) <= 0) continue;

                        $index = rand(0, count($classSubjectsTemp) - 1);

                        // Check vị trí trống
                        if ($result[$arrIndex][$rowIndex][$columnIndex] !== null) continue;

                        // //  Check phòng có phù hợp hay không (loại phòng, số lượng)
                        if ($typeRoom->ac_name != $classSubjectsTemp[$index]['type_room'] || $classSubjectsTemp[$index]['max_student'] > $roomQuantity) continue;

                        // //  Check tồn tại group thời điểm hiện tại
                        $checkGroup = 0;
                        for ($roomIndex = 0; $roomIndex < count($rooms); $roomIndex++) {
                            if ($result[$roomIndex][$rowIndex][$columnIndex] != null && $result[$roomIndex][$rowIndex][$columnIndex]['group'] == $classSubjectsTemp[$index]['group']) {
                                $checkGroup = 1;
                                break;
                            }
                        }
                        if ($checkGroup == 1) continue;

                        // // Check giáo viên dạy trùng ca
                        $teacherId = 0;
                        foreach ($assignment as $assignmentItem) {
                            if ($assignmentItem['class_subject_id'] == $classSubjectsTemp[$index]['id'])
                                $teacherId = $assignmentItem['teacher_id'];
                        }

                        $checkTeacher = 0;
                        for ($roomIndex = 0; $roomIndex < count($rooms); $roomIndex++) {
                            if ($result[$roomIndex][$rowIndex][$columnIndex] != null && $result[$roomIndex][$rowIndex][$columnIndex]['teacher_id'] == $teacherId) {
                                $checkTeacher = 1;
                                break;
                            }
                        }
                        if ($checkTeacher == 1) continue;

                        // Check 2 ca của học phần 4 tín chỉ phải cách nhau it nhất 2 ngày
                        if ($classSubjectsTemp[$index]['credits'] >= 4) {
                            $dayPoint = 0;
                            for ($roomIndex = 0; $roomIndex < count($rooms); $roomIndex++) {
                                for ($shiftIndex = 0; $shiftIndex < 2; $shiftIndex++) {
                                    for ($dayIndex = 0; $dayIndex < 6; $dayIndex++) {
                                        if ($result[$roomIndex][$shiftIndex][$dayIndex] != null && $result[$roomIndex][$shiftIndex][$dayIndex]['id'] == $classSubjectsTemp[$index]['id'])
                                            $dayPoint = $dayIndex;
                                    }
                                }
                            }

                            if ($dayPoint != 0) {
                                if ($dayPoint >= $columnIndex && $dayPoint - $columnIndex < 1) continue;
                                else if ($columnIndex > $dayPoint && $columnIndex - $dayPoint < 1) continue;
                            }
                        }

                        // Check lịch bận 1 vài giáo viên
                        $checkBusy = 0;
                        foreach ($teachers as $teacherItem) {
                            if ($teacherItem['id'] != $teacherId || $teacherItem['busy'] == null) continue;

                            foreach ($teacherItem['busy'] as $busyItem) {
                                $shift = $busyItem['ca'] == 's' ? 0 : $busyItem['ca'] == 'c' ? 1 : -1;
                                if ($shift != -1) {
                                    if ($shift == $rowIndex && $busyItem['thu'] - 2 == $columnIndex)
                                        $checkBusy = 1;
                                }
                            }
                        }
                        if ($checkBusy == 1) continue;

                        $date = new Carbon($date_start);
                        switch ($columnIndex + 2) {
                            case 2:
                                break;
                            case 3:
                                $date = $date->addDay();
                                break;
                            case 4:
                                $date = $date->addDays(2);
                                break;
                            case 5:
                                $date = $date->addDays(3);
                                break;
                            case 6:
                                $date = $date->addDays(4);
                                break;
                            case 7:
                                $date = $date->addDays(5);
                                break;
                            default:
                        }

                        $result[$arrIndex][$rowIndex][$columnIndex] = [
                            'room_id' => $roomId,
                            'subject_id' => $classSubjectsTemp[$index]['subject_id'],
                            'subject' => $classSubjectsTemp[$index]['shift_on_week'],
                            'type_room' => $classSubjectsTemp[$index]['type_room'],
                            'credits' => $classSubjectsTemp[$index]['credits'],
                            'max_student' => $classSubjectsTemp[$index]['max_student'],
                            'id' => $classSubjectsTemp[$index]['id'],
                            'group' => $classSubjectsTemp[$index]['group'],
                            'room_code' => Room::find($roomId)->code,
                            'day_of_week' => $columnIndex + 2,
                            'shift' => $rowIndex == 0 ? "s" : "c",
                            'date' => date('Y/m/d', strtotime($date)),
                            'teacher_id' => $teacherId,
                            'teacher_name' => Teacher::find($teacherId)->name,
                            'subject_name' => Subject::find($classSubjectsTemp[$index]['subject_id'])->name,
                            "class_subject_code" => ClassSubject::find($classSubjectsTemp[$index]['id'])->code,
                        ];

                        //  Check lớp học phần có 2 ca trong tuần trở lên thì giảm đi chứ không xoá
                        if ($classSubjectsTemp[$index]['shift_on_week'] > 1)
                            $classSubjectsTemp[$index]['shift_on_week'] = $classSubjectsTemp[$index]['shift_on_week'] - 1;
                        else
                            array_splice($classSubjectsTemp, $index, 1);
                    }
                }
            }

            if (count($classSubjectsTemp) <= 0) break;
        }

        return $result;
    }

    public function schedule_save(Request $request) {
        foreach ($request->data as $schedule) {
            $subject = Subject::find($schedule['subject_id']); 
            $weekTotal = ($subject->theory_lesson + $subject->practice_lesson) / 5;

            $date = new Carbon($schedule['date']);

            for ($i = 0; $i < $weekTotal; $i++) {
                $newData = [
                    'date' => (string) date('Y/m/d', strtotime($date)),
                    'day_on_week' => $schedule['day_of_week'],
                    'class_subject_id' => $schedule['id'],
                    'subject_id' => $schedule['subject_id'],
                    'teacher_id' => $schedule['teacher_id'],
                    'room_id' => $schedule['room_id'],
                ];

                if ($schedule['shift'] == 's') {
                    $newData['lesson_start'] = 2;
                    $newData['lesson_end'] = 6;
                }

                if ($schedule['shift'] == 'c') {
                    $newData['lesson_start'] = 8;
                    $newData['lesson_end'] = 12;
                }

                Schedule::create($newData);

                $date = $date->addWeek();
            }
        }

        $response = [
            'status' => 201,
            'success' => 'success',
        ];

        return response($response, 201);
    }
}
