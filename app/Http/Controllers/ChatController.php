<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        // Generate or get session ID
        $sessionId = $request->session()->get('chat_session_id', Str::uuid());
        $request->session()->put('chat_session_id', $sessionId);

        // Store the message
        $chat = Chat::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'session_id' => $sessionId
        ]);

        try {
            // First check if we have a predefined response
            $predefinedResponse = $this->getPredefinedResponse($request->message);
            
            if ($predefinedResponse) {
                $aiResponse = $predefinedResponse;
            } else {
                // Get AI response as fallback
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.huggingface.api_key'),
                ])
                ->withOptions([
                    'verify' => storage_path('app/cacert.pem')
                ])
                ->post('https://api-inference.huggingface.co/models/facebook/blenderbot-400M-distill', [
                    'inputs' => "You are a school management system assistant. " . $request->message,
                    'parameters' => [
                        'max_length' => 128,
                        'truncation' => true,
                        'temperature' => 0.7,
                        'top_p' => 0.9,
                        'do_sample' => true
                    ]
                ]);

                \Log::info('HuggingFace API Response:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'json' => $response->json()
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $aiResponse = $responseData[0]['generated_text'] ?? 'Sorry, I could not process your request.';
                    $aiResponse = $this->postProcessResponse($aiResponse);
                } else {
                    $aiResponse = 'Sorry, I could not process your request.';
                }
            }

            // Update the chat with AI response
            $chat->update(['response' => $aiResponse]);

            return response()->json([
                'message' => $chat->message,
                'response' => $chat->response,
                'timestamp' => $chat->created_at->diffForHumans()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error processing chat request:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $chat->update(['response' => 'Sorry, I could not process your request.']);

            return response()->json([
                'message' => $chat->message,
                'response' => $chat->response,
                'timestamp' => $chat->created_at->diffForHumans()
            ], 500);
        }
    }

    private function getPredefinedResponse($message)
    {
        // Convert message to lowercase for easier matching
        $message = strtolower($message);

        // Define common patterns and their responses
        $patterns = [
            // Report Cards
            'report card' => 'To access bulk report cards, go to the Reports section in your dashboard, select "Report Cards", and choose "Bulk Generation". You can then select the class and term for which you want to generate reports.',
            'report cards' => 'To access bulk report cards, go to the Reports section in your dashboard, select "Report Cards", and choose "Bulk Generation". You can then select the class and term for which you want to generate reports.',
            'bulk report' => 'To generate bulk reports, navigate to the Reports section, select your desired report type, and use the "Bulk Generation" option. You can filter by class, term, or date range.',
            
            // Attendance
            'attendance' => 'You can access attendance records through the Attendance section in your dashboard. Here you can view daily attendance, generate reports, and manage attendance records.',
            'mark attendance' => 'To mark attendance, go to the Attendance section, select the class, and you can mark attendance for individual students or use the bulk attendance feature.',
            
            // Grades
            'grades' => 'Access grades through the Grades section in your dashboard. You can view individual student grades or generate class-wide grade reports.',
            'marks' => 'Student marks can be accessed and managed through the Grades section. You can enter individual marks or use the bulk upload feature.',
            
            // Settings & Profile
            'password' => 'To change your password, go to Profile Settings in the top-right menu and select "Change Password".',
            'profile' => 'Your profile settings can be accessed by clicking your profile picture in the top-right corner.',
            
            // Help
            'help' => 'I can help you navigate the school management system. What specific feature do you need assistance with?',
            'how do i' => 'I can help you with that. Please specify which feature you\'re trying to access, and I\'ll provide step-by-step instructions.',
            
            // Students
            'student info' => 'Student information can be accessed through the Students section. Note that access is restricted based on your user permissions.',
            'add student' => 'To add a new student, go to the Students section and click the "Add Student" button. Fill in the required information in the form.',
            
            // Default
            'default' => null
        ];

        // Check for matching patterns
        foreach ($patterns as $pattern => $response) {
            if (str_contains($message, $pattern)) {
                return $response;
            }
        }

        return null;
    }

    private function postProcessResponse($response)
    {
        // Clean up the response
        $response = preg_replace('/^Assistant:\s*/i', '', $response);
        
        // Add system-specific context if needed
        if (stripos($response, 'help') !== false) {
            $response .= ' You can find most features in the main dashboard menu.';
        }
        
        if (stripos($response, 'access') !== false) {
            $response .= ' Remember to ensure you have the proper permissions.';
        }

        return $response;
    }

    public function history(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id');
        
        $chats = Chat::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($chats);
    }
} 