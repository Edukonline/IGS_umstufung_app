<?php
namespace OCA\KursUmstufung\Exception;

/**
 * Wird geworfen, wenn Eingabedaten an einer Trust Boundary die
 * fachlichen Regeln verletzen. Der Controller mappt sie auf HTTP 400.
 */
class ValidationException extends \Exception {
}
