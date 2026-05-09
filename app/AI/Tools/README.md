# Tools Directory

Place standalone Prism Tool classes here.

Example:

```php
use Prism\Prism\Tool;

$tool = (new Tool())
    ->as('tool_name')
    ->for('Tool description')
    ->withStringParameter('param', 'Parameter description')
    ->using(function(string $param): string {
        return json_encode(['result' => $param]);
    });
```

For LarAgent tools, use #[Tool] attribute methods in your Agent class instead.
