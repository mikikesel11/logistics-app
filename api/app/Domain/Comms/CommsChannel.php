<?php

namespace App\Domain\Comms;

/**
 * SEAM (not yet implemented) — the unified communications pillar.
 *
 * A CommsChannel is a provider that can send and receive messages on one
 * medium (SMS, voice, email, ...). The MVP does not build this; the interface
 * exists so the pillar can be added without reshaping the CRM. Planned first
 * implementation: a Twilio SMS channel.
 *
 * @see docs/domain-model.md ("Extension seams")
 */
interface CommsChannel
{
    /** Stable identifier, e.g. "twilio_sms", "email". */
    public function name(): string;

    /**
     * Send a message to a recipient (phone/email depending on the channel).
     * Returns a provider message id.
     */
    public function send(string $to, string $body): string;
}
