<?php
namespace App\AiAgents;

use LarAgent\Agent;
use Illuminate\Support\Facades\View;

class SupportAgent1 extends Agent
{
    protected $provider; // ✅ default provider key
    protected $model;// ✅ default model
    protected $history = 'in_memory';
    protected $tools = [];

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
        return View::make('agents.support_prompt', [
            'instructions' => $this->instructions(),
            'message' => $message,
        ])->render();
    }
}
