<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText;

use Doctrine\Common\EventManager;
use phpDocumentor\Guides\Environment;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Parser as ParserInterface;
use phpDocumentor\Guides\ReferenceRegistry;
use phpDocumentor\Guides\References\Doc;
use phpDocumentor\Guides\References\Reference;
use phpDocumentor\Guides\RestructuredText\Directives\Directive;
use phpDocumentor\Guides\RestructuredText\Formats\Format;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParser;
use RuntimeException;

use function array_merge;

class Parser implements ParserInterface
{
    /** @var Environment|null */
    private $environment;

    /** @var Directive[] */
    private $directives;

    /** @var string|null */
    private $filename = null;

    /** @var DocumentParser|null */
    private $documentParser;

    /** @var array<Reference> */
    private $references;

    /** @var EventManager */
    private $eventManager;

    /** @var Format */
    private $format;

    /** @var ReferenceRegistry */
    private $referenceRegistry;

    /**
     * @param array<Directive> $directives
     * @param array<Reference> $references
     */
    public function __construct(
        Format $format,
        ReferenceRegistry $referenceRegistry,
        EventManager $eventManager,
        array $directives,
        array $references
    ) {
        $this->format = $format;
        $this->directives = $directives;
        $this->referenceRegistry = $referenceRegistry;
        $this->references = $references;
        $this->eventManager = $eventManager;

        $this->initDirectives($directives);
        $this->initReferences($references);
    }

    public function getSubParser(): Parser
    {
        return new Parser(
            $this->format,
            $this->referenceRegistry,
            $this->eventManager,
            $this->directives,
            $this->references
        );
    }

    /**
     * @param array<Directive> $directives
     */
    public function initDirectives(array $directives): void
    {
        $directives = array_merge(
            $directives,
            $this->format->getDirectives()
        );

        foreach ($directives as $directive) {
            $this->registerDirective($directive);
        }
    }

    /**
     * @param array<Reference> $references
     */
    public function initReferences(array $references): void
    {
        $references = array_merge(
            [
                new Doc(),
                new Doc('ref', true),
            ],
            $references
        );

        foreach ($references as $reference) {
            $this->referenceRegistry->registerReference($reference);
        }
    }

    public function getEnvironment(): Environment
    {
        if ($this->environment === null) {
            throw new RuntimeException(
                'A parser\'s Environment should not be consulted before parsing has started'
            );
        }

        return $this->environment;
    }

    public function registerDirective(Directive $directive): void
    {
        $this->directives[$directive->getName()] = $directive;
        foreach ($directive->getAliases() as $alias) {
            $this->directives[$alias] = $directive;
        }
    }

    public function getDocument(): DocumentNode
    {
        if ($this->documentParser === null) {
            throw new RuntimeException('Nothing has been parsed yet.');
        }

        return $this->documentParser->getDocument();
    }

    public function getFilename(): string
    {
        return $this->filename ?: '(unknown)';
    }

    public function parse(Environment $environment, string $contents): DocumentNode
    {
        $environment->reset();

        return $this->parseLocal($environment, $contents);
    }

    public function parseLocal(Environment $environment, string $contents): DocumentNode
    {
        $this->environment = $environment;
        $this->documentParser = $this->createDocumentParser();

        return $this->documentParser->parse($contents);
    }

    public function parseFragment(string $contents): DocumentNode
    {
        return $this->createDocumentParser()->parse($contents);
    }

    private function createDocumentParser(): DocumentParser
    {
        return new DocumentParser(
            $this,
            $this->eventManager,
            $this->directives
        );
    }

    public function getReferenceRegistry(): ReferenceRegistry
    {
        return $this->referenceRegistry;
    }
}
