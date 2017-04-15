<?php

namespace Dictionary\Action;

use Common\Action\ActionInterface;
use Common\Container\ConfigInterface;
use Dictionary\Entity\Dictionary;
use Dictionary\Transformer\DictionaryTransformer;
use Interop\Http\ServerMiddleware\DelegateInterface;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Http\Response;

/**
 * Class OfflineDictionary
 * @package Common\Adapter
 */
class OfflineDictionaryAction implements ActionInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * OfflineDictionaryAction constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        /* Set locale for read russian text */
        $locale = 'ru_RU.UTF-8';
        setlocale(LC_ALL, $locale);
        putenv('LC_ALL='.$locale);

        $text = (string)$request->getAttribute('text');
        $path = $this->config->get('dictionary.path.offline');

        $transcription = [];
        $example = '';
        foreach (explode(' ', $text) as $string) {
            $cmd = "sdcv --data-dir " . $path . " -n --utf8-output --utf8-input "
                . escapeshellarg($string);
            $output = shell_exec(
                $cmd
            );
            /* Получение транскрипции из разных словарей */
            /* <t>transcription</t> */
            preg_match('~\<t\>(.*)\<\/t\>~U', $output, $matches);
            /* [transcription] */
            preg_match('~\[(.*)\]~U', $output, $newMatches);
            if (isset($matches[1])) {
                $transcription[] = $matches[1];
            } elseif (isset($newMatches[1])) {
                $transcription[] = $newMatches[1];
            }

            if (str_word_count($text) == 1) {
                preg_match_all('~\d\>\s(\_.*\.\s)*(.*)~', $output, $matches);
                $tmpTranslations = $matches[2];

                foreach ($tmpTranslations as $row) {
                    $tmpExamples = explode(' _Ex: ', $row);
                    foreach ($tmpExamples as $tmpExample) {
                        preg_match('~^([\w\s\']+)\s?~', $tmpExample, $matches);
                        if (!empty($matches)) {
                            $example = trim($matches[0]);
                            break 2;
                        }
                    }
                }
            }
        }

        $dictionary = new Dictionary();
        $dictionary->setId(0);
        $dictionary->setText($text);
        $dictionary->setTranscription(implode(' ', $transcription));
        $dictionary->setExample($example);

        $item = new Item($dictionary, new DictionaryTransformer(), $this->getResourceName());

        $request = $request
            ->withAttribute(self::RESPONSE, $item)
            ->withAttribute(self::HTTP_CODE, Response::STATUS_CODE_200);

        return $delegate->process($request);
    }

    public function getResourceName(): string
    {
        return 'offline';
    }
}
