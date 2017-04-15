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
class ApresyanDictionaryAction implements ActionInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * ApresyanDictionaryAction constructor.
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
        $text = urldecode((string)$request->getAttribute('text'));
        $path = $this->config->get('dictionary.path.apresyan');

        $example = '';
        foreach (explode(' ', $text) as $string) {
            $output = shell_exec("sdcv " . $path . " " . escapeshellarg($string));

            /* Получение всех аглийских примеров примеров более 3х символов */
            preg_match_all('~[^Found]^[a-zA-Z\s\,\.\(\)\']{5,}~m', $output, $matches);
            /* Удаление пробелов у всех строк */
            $matches = array_map('trim', $matches[0]);
            /* Сортировка строк по длине, по убыванию */
            usort(
                $matches,
                function ($first, $second) {
                    $firstLen = strlen($first);
                    $secondLen = strlen($second);
                    if ($firstLen == $secondLen) {
                        return 0;
                    }
                    return $firstLen > $secondLen ? -1 : 1;
                }
            );

            /* Удаление пустых строк и строк больше 90 символов */
            foreach ($matches as $key => $value) {
                if (empty($value) || strlen($value) > 90) {
                    unset($matches[$key]);
                }
            }

            $example = array_shift($matches);
        }

        $dictionary = new Dictionary();
        $dictionary->setId(0);
        $dictionary->setText($text);
        $dictionary->setExample($example);

        $item = new Item($dictionary, new DictionaryTransformer(), $this->getResourceName());

        $request = $request
            ->withAttribute(self::RESPONSE, $item)
            ->withAttribute(self::HTTP_CODE, Response::STATUS_CODE_200);

        return $delegate->process($request);
    }

    public function getResourceName(): string
    {
        return 'apresyan';
    }
}
