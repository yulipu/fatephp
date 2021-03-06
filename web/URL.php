<?php
/**
 * @author
 * @license MIT
 */
namespace fate\web;

/**
 * Uniform Resource Location
 *
 * @see https://tools.ietf.org/html/rfc1738
 */
class URL {

    /**
     * 创建一个 url
     *
     * eg.
     *
     * // scheme://host/index/index
     * url.to('index/index')
     *
     * // scheme://host/index/index?id=1#anchor
     * url.to('index/index', [id => 1, '#' => 'anchor'])
     *
     * @param string $url
     * @param array $parameters
     * @return string
     */
    public static function to($url, $parameters = null) {
        $host = Request::getInstance()->getHostInfo();
        $query = '';
        $anchor = '';

        $url = $host . '/' . $url;

        if(null !== $parameters) {
            if(isset($parameters['#'])) {
                $anchor = $parameters['#'];
                unset($parameters['#']);
            }

            foreach($parameters as $k => $v) {
                $query = $query . $k . '=' . $v . '&';
            }
            $query = rtrim($query, '&');

            if('' !== $query) {
                $url = $url . '?' . $query;
            }
            if('' !== $anchor) {
                $url = $url . '#' . $anchor;
            }
        }

        return $url;
    }

}
