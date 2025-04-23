<?php

namespace GrizzlyGarillaz\LaravelTagger\Traits;

use GrizzlyGarillaz\LaravelTagger\Exceptions\EmptyTagFound;
use GrizzlyGarillaz\LaravelTagger\Exceptions\UnparsableTagFound;
use Illuminate\Support\Str;

/** @phpstan-ignore trait.unused */
trait Taggable
{
    /** @var array<string,callable>|null */
    private ?array $tagMappings = null;

    /**
     * Return an array of model tags
     */
    public static function getTags(): array
    {
        return (new self)->collectTags();
    }

    private function collectTags(): array
    {
        return array_keys($this->collectTagMappings());
    }

    private function collectTagMappings(?array $only = null): array
    {
        if ($this->tagMappings === null) {
            $this->tagMappings = [];
            foreach ($this->taggable() as $raw => $val) {
                $name = is_numeric($raw) ? $val : $raw;
                $tag = Str::wrap(Str::upper(Str::snake($name)), '::');

                $this->tagMappings[$tag] = is_callable($val)
                    ? fn() => $val($this)
                    : fn() => $this->{$val};
            }
        }

        if ($only !== null) {
            $only = array_map(
                static fn($t) => Str::wrap(Str::upper(Str::snake($t)), '::'),
                $only
            );

            return array_intersect_key($this->tagMappings, array_flip($only));
        }

        return $this->tagMappings;
    }

    /**
     * Define your own fields here:
     *
     *   return [
     *     'name',                         // will call $model->name
     *     'dob' => fn($m)=> $m->dob->format('d/m/Y'),
     *   ];
     */
    protected function taggable(): array
    {
        return [];
    }

    /**
     * Parse $text, replacing only the tags present, enforcing
     * non‑empty (unless optional) and blowing up on any leftover ::TAG::.
     *
     * @throws EmptyTagFound
     * @throws UnparsableTagFound
     */
    public function parse(string $text): string
    {
        return preg_replace_callback(
            pattern: '/(::\w+::)/u',
            callback: function (array $m) {
                $tag = $m[1];
                $lower = Str::lower(trim($tag, ':'));
                $mappings = $this->collectTagMappings([$lower]);
                if (!isset($mappings[$tag])) {
                    throw new UnparsableTagFound("Failed to parse tag: {$tag}");
                }
                $value = $mappings[$tag]($this);
                if (empty($value) && !in_array($tag, $this->optionalTags(), true)) {
                    throw new EmptyTagFound("Tag {$tag} is empty");
                }

                return $value;
            },
            subject: $text,
        ) ?? $text;
    }

    /**
     * Tags you’re ok with being empty; they’ll be skipped
     * instead of throwing EmptyTagFound.
     */
    protected function optionalTags(): array
    {
        return [];
    }

    /**
     * Return an array of current model tags with their values
     */
    public function tags(?array $only = null): array
    {
        return array_map(static fn($cb) => $cb(), $this->collectTagMappings($only));
    }
}
