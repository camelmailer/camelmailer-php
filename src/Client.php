<?php

declare(strict_types=1);

namespace CamelMailer;

use CamelMailer\Contracts\TransporterInterface;
use CamelMailer\Resources\Bounces;
use CamelMailer\Resources\Dmarc;
use CamelMailer\Resources\Emails;
use CamelMailer\Resources\Stats;
use CamelMailer\Resources\Streams;
use CamelMailer\Resources\Templates;

final class Client
{
    public readonly Emails $emails;

    public readonly Templates $templates;

    public readonly Streams $streams;

    public readonly Stats $stats;

    public readonly Bounces $bounces;

    public readonly Dmarc $dmarc;

    public function __construct(private readonly TransporterInterface $transporter)
    {
        $this->emails = new Emails($transporter);
        $this->templates = new Templates($transporter);
        $this->streams = new Streams($transporter);
        $this->stats = new Stats($transporter);
        $this->bounces = new Bounces($transporter);
        $this->dmarc = new Dmarc($transporter);
    }

    /**
     * Validate the server API key.
     */
    public function ping(): ApiObject
    {
        return ApiObject::from($this->transporter->request('GET', '/api/v2/server/ping'));
    }

    /**
     * Show the authenticated server.
     */
    public function server(): ApiObject
    {
        return ApiObject::from($this->transporter->request('GET', '/api/v2/server/'));
    }
}
