<?php
/**
 * MP4PHP
 * PHP library for parsing and modifying MP4 files
 *
 * Copyright © 2016-2020 Markus Baumer <markus@baumer.dev>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See
 * the GNU General Public License for more details.
 */

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in([__DIR__.'/src', __DIR__.'/tests']);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        // PHP7x1Migration:risky
        '@PHP7x1Migration' => true,
        '@PHP7x1Migration:risky' => true,

        // PHP7x3Migration
        '@PHP7x3Migration' => true,

        // Symfony
        '@Symfony' => true,
        'phpdoc_summary' => false,
        'phpdoc_separation' => true,
        'array_syntax' => ['syntax' => 'short'],

        // Symfony:risky
        '@Symfony:risky' => true,
        'non_printable_character' => ['use_escape_sequences_in_strings' => true],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'modernize_types_casting' => true,
        'yoda_style' => ['equal' => null, 'identical' => null, 'less_and_greater' => null],

        // PhpCsFixer
        'ordered_class_elements' => true,
        'phpdoc_order' => true,
        'phpdoc_no_empty_return' => false,

        // PhpCsFixer:risky
        'strict_comparison' => true,

        // In no preset
        'declare_strict_types' => true,
        'blank_line_after_opening_tag' => false,
        'no_php4_constructor' => true,
        'global_namespace_import' => true,
        'list_syntax' => ['syntax' => 'short'],
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_line_span' => ['const' => 'single', 'property' => 'multi', 'method' => 'multi'],
    ])
;
