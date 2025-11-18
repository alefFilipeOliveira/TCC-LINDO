
<?php
// UPAMED configuration
// Leave OPENAI_API_KEY empty to use simulated IA
return [
  'OPENAI_API_KEY' => '',
  'MODEL' => 'gpt-4o-mini',
  // MySQL settings (fill to enable DB storage)
  'DB_HOST' => 'localhost',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'DB_NAME' => 'upamed_db'
];
?>