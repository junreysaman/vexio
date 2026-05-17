<?php

declare(strict_types=1);

namespace Framework;

/**
 * --------------------------------------------------------------------------
 * TemplateEngine
 * --------------------------------------------------------------------------
 * Lightweight PHP template system inspired by Blade and Plates.
 * 
 * ✦ Now supports:
 *   - Global + page-specific data merging
 *   - Section handling (start/end/section)
 *   - Layouts (default or per render)
 *   - Partial includes
 *   - Hybrid asset system (styles/scripts)
 */
class TemplateEngine
{
    /** @var array<string, mixed> */
    private array $globalData = [
        'styles' => [],
        'scripts' => [],
    ];

    /** @var ?string Default layout used when none specified */
    private ?string $defaultLayout = null;

    /** @var array<string, string> Captured sections */
    private array $sections = [];

    /** @var ?string Currently active section */
    private ?string $currentSection = null;

    /** @var array<int, array<string, mixed>> Variables available to nested partials */
    private array $renderStack = [];

    public function __construct(private string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    // ---------------------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------------------

    /** Sets the default layout */
    public function setDefaultLayout(string $layout): void
    {
        $this->defaultLayout = $layout;
    }

    /** Sets or replaces a global variable */
    public function addGlobal(string $key, mixed $value): void
    {
        $this->globalData[$key] = $value;
    }

    /** Appends to a global array (useful for styles/scripts) */
    public function appendGlobal(string $key, mixed $value): void
    {
        if (!isset($this->globalData[$key]) || !is_array($this->globalData[$key])) {
            $this->globalData[$key] = [];
        }
        $this->globalData[$key][] = $value;
    }

    // ---------------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------------

    /**
     * Renders a view and optionally wraps it in a layout.
     *
     * @param string $view   The view path (relative to basePath)
     * @param string|null $layout Optional layout override (null = use default)
     * @param array $data    Template variables to inject
     */
    public function render(string $view, ?string $layout = null, array $data = []): string
    {
        // Merge global and page-level data (deep merge for styles/scripts)
        $vars = $this->mergeRecursiveDistinct($this->globalData, $data);

        // Reset sections per render
        $this->sections = [];

        // Render main content
        $content = $this->renderFile($view, $vars);

        // Determine layout
        $layoutPath = $layout ?? $this->defaultLayout;

        if ($layoutPath) {
            $vars['content'] = $content;
            return $this->renderFile($layoutPath, $vars);
        }

        return $content;
    }

    // ---------------------------------------------------------------------
    // Sections
    // ---------------------------------------------------------------------

    public function start(string $name): void
    {
        if ($this->currentSection !== null) {
            throw new \LogicException("Cannot start section '{$name}' before ending '{$this->currentSection}'.");
        }
        $this->currentSection = $name;
        ob_start();
    }

    public function end(): void
    {
        if ($this->currentSection === null) {
            throw new \LogicException("No active section to end.");
        }
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    public function section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    // ---------------------------------------------------------------------
    // Partials & Utilities
    // ---------------------------------------------------------------------

    public function includePartial(string $path, array $data = []): string
    {
        $currentVars = end($this->renderStack) ?: [];

        return $this->renderFile($path, array_merge($this->globalData, $currentVars, $data));
    }

    public function exists(string $path): bool
    {
        try {
            $this->resolve($path);
            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }

    // ---------------------------------------------------------------------
    // Internal Helpers
    // ---------------------------------------------------------------------

    private function resolve(string $path): string
    {
        $file = str_ends_with($path, '.php') ? $path : $path . '.php';
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . ltrim($file, '/\\');

        if (!is_file($fullPath)) {
            throw new \RuntimeException("Template not found: {$fullPath}");
        }

        return $fullPath;
    }

    private function renderFile(string $template, array $vars): string
    {
        $filePath = $this->resolve($template);

        $render = function (string $__filePath, array $__vars): string {
            $this->renderStack[] = $__vars;
            extract($__vars, EXTR_SKIP);
            ob_start();
            try {
                include $__filePath;
                return ob_get_clean();
            } finally {
                array_pop($this->renderStack);
            }
        };

        return $render($filePath, $vars);
    }

    /**
     * Recursively merges two arrays, preserving numeric keys separately.
     */
    private function mergeRecursiveDistinct(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
