<?php

class Controller_PageProfiler extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        $db = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('db');
        $profiler = $db->getProfiler();

        $totalQueries = $profiler->getTotalNumQueries();
        $queryTime    = $profiler->getTotalElapsedSecs();

        $longestTime = 0;
        $longestQuery = null;
        $queries = $profiler->getQueryProfiles();

        $content = " <br />\n";//"Executed $totalQueries database queries in $queryTime seconds<br />\n";

        if ($queries !== false) { // loop over each query issued
            foreach ($queries as $query) {
                $content .= "\n<!-- Query (" . $query->getElapsedSecs() . "s): " . $query->getQuery() . "\n\n -->\n";
                if ($query->getElapsedSecs() > $longestTime) {
                    $longestTime = $query->getElapsedSecs();
                    $longestQuery = htmlspecialchars(addcslashes($query->getQuery(), '"'));
                }
            }

            $content .= "Longest query time: $longestTime."
                       ." <a href=\"#\" onclick=\"return false\" title=\"$longestQuery\">Mouseover for query</a><br />\n";
        }

        // Log $content here, or append it to the response body

        $this->getResponse()->setBody($this->getResponse()->getBody() .
                                      $content);
    }
}