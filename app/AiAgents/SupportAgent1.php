<?php
namespace App\AiAgents;


use LarAgent\Agent;
use App\Helpers\UsersData;
use LarAgent\Attributes\Tool;
use App\Helpers\UserTransactions;
use Illuminate\Support\Facades\View;

class SupportAgent1 extends Agent
{
    protected $provider;
    protected $model;
    protected $history = 'in_memory';



    public function __construct(string $provider, string $session_id, ?string $model = null)
    {
       
        $this->provider = $provider;
        if ($model) {
            $this->model = $model;
        }

        parent::__construct($session_id); // ✅ must be last
    }

    public function instructions()
    {
        return View::make('agents.support_instructions')->render();
    }

    public function prompt($message)
    {
        // Pass both message and instructions to Blade view
        \Log::info('SupportAgent1 prompt called with message: ' . $message);
        return View::make('agents.support_prompt', [
            'instructions' => $this->instructions(),
            'message' => $message,
        ])->render();
    }

    #[Tool('Service: Get user information and profile data by user ID')]
    public function getUserData(string $id): array
    {
        if (!$this->isUserId($id)) {
            return ['error' => 'Invalid user ID format. User ID must be numeric.'];
        }
        
        $user = UsersData::findById((int) $id);
        \Log::info('getUserData called with ID: ' . $id . ', result: ' . json_encode($user));
        return $user ?? [];
    }

    #[Tool('Service: Get transaction details and history by transaction ID')]
    public function getByTransactionId(string $transactionId): array
    {
        if (!$this->isTransactionId($transactionId)) {
            return ['error' => 'Invalid transaction ID format. Transaction ID must start with capital T followed by hyphen and numbers (e.g., T-01, T-02).'];
        }
        
        $transaction = UserTransactions::getByTransactionId($transactionId);
        return $transaction;
    }
    

    public function isTransactionId(?string $input): bool
    {
        return preg_match('/^T-\d+$/', trim($input)) === 1;
    }
    
  
    public function isUserId(string $input): bool
    {
        return is_numeric(trim($input));
    }
}
