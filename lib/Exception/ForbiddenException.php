<?php
namespace OCA\KursUmstufung\Exception;

/**
 * Wird geworfen, wenn ein Nutzer eine Aktion ausführt, zu der er nicht
 * berechtigt ist (z.B. fremder Antrag, falscher Status). Mapped auf HTTP 403.
 */
class ForbiddenException extends \Exception {
}
