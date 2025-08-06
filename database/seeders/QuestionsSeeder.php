<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $questions = [
            [
                'question_key' => 'name',
                'question_text' => 'What is your name?',
                'order' => 1,
                'is_required' => true
            ],
            [
                'question_key' => 'country',
                'question_text' => 'Which country are you from?',
                'order' => 2,
                'is_required' => true
            ],
            [
                'question_key' => 'region',
                'question_text' => 'What region or city are you located in?',
                'order' => 3,
                'is_required' => false
            ],
            [
                'question_key' => 'service_type',
                'question_text' => 'What type of service do you need? (repair services, product supply, or car driver/chauffeur)',
                'order' => 4,
                'is_required' => true
            ]
        ];

        foreach ($questions as $question) {
            Question::updateOrCreate(
                ['question_key' => $question['question_key']],
                $question
            );
        }
    }
}
