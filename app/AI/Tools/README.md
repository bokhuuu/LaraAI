# Tools Directory

Standalone Prism Tool classes live here.
Use these when wiring tools directly into ToolService.
For agent tools, use the #[Tool] attribute inside your Agent class instead.

## Example:

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
