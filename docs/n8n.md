N8N integration

Env variables

- N8N_API_KEY=some_long_secret
- N8N_SUBJECT_SIMILARITY_THRESHOLD=60   # integer percent (default 55)

Authentication options

1) Static API key
- Configure `X-N8N-API-KEY` header with the value of `N8N_API_KEY`.

2) Laravel Sanctum personal access token
- Create a token for an existing user (via tinker):

```bash
php artisan tinker
$user = \App\Models\User::find(1); // user for n8n actions
$token = $user->createToken('n8n')->plainTextToken;
exit
```
- In n8n set `Authorization: Bearer <plainTextToken>` on HTTP Request nodes.

Install Sanctum (if not present)

Run these commands locally on the server to install and enable Sanctum:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Notes:
- The package adds the `PersonalAccessToken` model used for `createToken()`.
- Use `auth:sanctum` middleware on routes that must validate tokens.
- For CLI token creation avoid pager by printing only the token:

```bash
php artisan tinker --execute "echo \\App\\Models\\User::find(1)->createToken('n8n')->plainTextToken;"
```

Endpoint

- POST /api/n8n/email-check
  - Body JSON: `{ "email": "optional@example.com", "subject": "El asunto del correo" }`
  - Headers: `X-N8N-API-KEY` or `Authorization: Bearer <token>`
  - Response examples:
    - No match: `{ "matched": false }`
    - Email match: `{ "matched": true, "match_type": "email", "client": { ... } }`
    - Similarity match: `{ "matched": true, "match_type": "similarity", "score": 78, "client": { ... } }`

Notes

- The controller accepts either the static `N8N_API_KEY` or a Sanctum personal access token when Sanctum is installed.
- Adjust `N8N_SUBJECT_SIMILARITY_THRESHOLD` to tune false positives.
- Consider creating a dedicated `n8n` user with minimal roles and a token for better auditing.
- For pushes from the app (webhooks) we recommend signing payloads with HMAC and verifying on the n8n side.
