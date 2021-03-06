<?php
/**
 * @author
 * @license MIT
 */
namespace fate\log;

/**
 * 日志接口
 */
interface ILog {
    
    /**
     * flush log
     *
     * @param array $message the message to be logged
     */
    public function flush($messages);
    
}
