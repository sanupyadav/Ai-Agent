import sys
import json
import os
from openai import OpenAI

def main():
    # Get API key from environment variable
    a4f_api_key = os.getenv('A4F_API_KEY', 'ddc-a4f-d5d100db188f414fbf505a57f8b22b00')
    a4f_base_url = "https://api.a4f.co/v1"

    client = OpenAI(
        api_key=a4f_api_key,
        base_url=a4f_base_url,
    )

    try:
        # Get user input and model from command-line arguments
        user_input = sys.argv[1] if len(sys.argv) > 1 else "Explain the concept of API gateways."
        model = sys.argv[2] if len(sys.argv) > 2 else "provider-2/gemini-2.0-flash"

        # Make API call
        completion = client.chat.completions.create(
            model=model,
            messages=[
                {"role": "system", "content": "You are a helpful assistant."},
                {"role": "user", "content": user_input}
            ],
            temperature=0.7,
            max_tokens=150,
        )

        # Return response as JSON
        response = {
            "status": "success",
            "result": completion.choices[0].message.content,
            "model": model
        }
        print(json.dumps(response))

    except Exception as error:
        # Return error as JSON
        response = {
            "status": "error",
            "message": str(error),
            "model": model if 'model' in locals() else None
        }
        print(json.dumps(response))

if __name__ == "__main__":
    main()