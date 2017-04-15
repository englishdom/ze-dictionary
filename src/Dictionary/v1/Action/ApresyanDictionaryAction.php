<?php

namespace Dictionary\Action;

use Common\Action\ActionInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OfflineDictionary
 * @package Common\Adapter
 */
class ApresyanDictionaryAction implements ActionInterface
{

    /**
     * @var string
     */
    protected $parameters = false;

    /**
     * @param string $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getParameters()
    {
        if (false === $this->parameters) {
            throw new RuntimeException('Parameters for dictionary must be set');
        }

        return $this->parameters;
    }

    public function getResourceName(): string
    {
        return 'apresyan';
    }

    /**
     * @param string $text
     * @param $content
     * @return array
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $example = '';
        foreach (explode(' ', $text) as $string) {
            $output = shell_exec("sdcv " . $this->getParameters() . " " . escapeshellarg($string));

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

        return $example;
    }

    /**
     * @param $path
     * @param int $chmod
     * @return mixed
     */
    public function create($path, $chmod = 0777)
    {
    }

    /**
     * @return mixed
     */
    public function update()
    {
    }

    /**
     * @param $path
     * @return mixed
     */
    public function delete($path)
    {
    }
}
