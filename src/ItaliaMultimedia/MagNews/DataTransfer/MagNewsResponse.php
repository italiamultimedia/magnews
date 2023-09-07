<?php

declare(strict_types=1);

namespace ItaliaMultimedia\MagNews\DataTransfer;

final class MagNewsResponse
{
    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param ?array<mixed> $errors
     * @param ?array<mixed> $sendemail
     * @param ?array<mixed> $enterworkflow
     * @phpcs:enable
     */
    public function __construct(
        public readonly bool $ok,
        public readonly string $pk,
        public readonly int $idcontact,
        public readonly string $action,
        public readonly ?array $errors,
        public readonly ?array $sendemail,
        public readonly ?array $enterworkflow,
    ) {
    }
}
