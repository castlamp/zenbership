<?php

class news extends db {

    protected $region = array();
    protected $regionData;
    protected $articles;
    protected $totalArticles;
    protected $data;
    protected $addQuery;
    protected $page = 1;
    protected $display = 0;
    protected $displayLock = false;
    protected $finalHtml = '';
    protected $filter;
    protected $loggedIn;


    public function __construct($region = '', $admin = false)
    {
        if (! $admin) {
            $session = new session();
            $ses     = $session->check_session();
            if ($ses['error'] == '1') {
                $this->loggedIn = false;
                $this->addQuery = " AND ppSD_login_announcements.public = '1'";
            } else {
                $this->loggedIn = true;
            }
        }

        if (! empty($region)) {
            $this->setRegion($region);
        }
    }


    public function __toString()
    {
        return $this->finalHtml;
    }


    /**
     * @param $region
     *
     * @return $this
     */
    public function setRegion($region)
    {
        $foundData = $this->regionData($region);

        return $this;
    }

    /**
     * @param $page
     *
     * @return $this
     */
    public function setPage($page)
    {
        if (is_numeric($page) && $page > 0) {
            $this->page = $page;
        }

        return $this;
    }

    /**
     * @param $display
     */
    public function setDisplay($display, $lock = false)
    {
        // If the news region has fixed settings users
        // cannot overwrite this.
        if ($this->displayLock)
            return $this;

        if (is_numeric($display) && $display > 0) {
            $this->display = $display;

            $this->displayLock = $lock;
        }

        return $this;
    }

    /**
     * @param $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = trim($filter);

        return $this;
    }


    public function getRegionData()
    {
        return $this->regionData;
    }


    /**
     * @param $region
     */
    protected function regionData($region)
    {
        $data = $this->get_array("
            SELECT *
            FROM ppSD_login_announcement_regions
            WHERE `tag`='" . $this->mysql_clean($region) . "'
            LIMIT 1
        ");

        if (empty($data['id']))
            return false;

        $this->region = $region;

        $this->regionData = $data;

        if (! empty($data['display'])) {
            $this->setDisplay($data['display'], true);
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @param array $data
     *
     * @return  $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }


    /**
     * @return array
     */
    protected function getArticlesInRegion($id = '')
    {
        if (! empty($id)) {
            $useRegion = $id;
        } else {
            $useRegion = $this->region;
        }

        if (empty($this->display)) {
            $this->display = 10;
        }

        if (! empty($_GET['id'])) {
            $this->addQuery .= " AND ppSD_login_announcements.id != '" . $this->mysql_cleans($_GET['id']) . "'";
        }

        $low = ($this->display * $this->page) - $this->display;

        $queryA = $this->get_array("
            SELECT
                COUNT(*) as total
            FROM
                `ppSD_login_annoucement_location`
            JOIN
                `ppSD_login_announcements`
                    ON ppSD_login_announcements.id = ppSD_login_annoucement_location.news_id
            WHERE
                ppSD_login_annoucement_location.region = '" . $this->mysql_clean($useRegion) . "' AND
                ppSD_login_announcements.active = '1' AND
                ppSD_login_announcements.starts <= '" . current_date() . "' AND
                (
                    ppSD_login_announcements.ends > '" . current_date() . "' OR
                    ppSD_login_announcements.ends = '1920-01-01 00:01:01'
                )
                $this->addQuery
        ");

        $this->totalArticles = $queryA['total'];

        $query = "
            SELECT
                ppSD_login_announcements.*
            FROM
                `ppSD_login_annoucement_location`
            JOIN
                `ppSD_login_announcements`
                    ON ppSD_login_announcements.id = ppSD_login_annoucement_location.news_id
            WHERE
                ppSD_login_annoucement_location.region = '" . $this->mysql_clean($useRegion) . "' AND
                ppSD_login_announcements.active = '1' AND
                ppSD_login_announcements.starts <= '" . current_date() . "' AND
                (
                    ppSD_login_announcements.ends > '" . current_date() . "' OR
                    ppSD_login_announcements.ends = '1920-01-01 00:01:01'
                )
                $this->addQuery
            ";

        if (! empty($this->filter)) {
            $query .= "AND MATCH(content) AGAINST('" . $this->filter . "' IN NATURAL LANGUAGE MODE)";
        }

        $query .= "
            ORDER BY
                ppSD_login_announcements.starts DESC
            LIMIT
                " . $low . "," . $this->display;

        $data = $this->run_query($query);

        $this->data = array();

        while ($row = $data->fetch()) {
            $this->data[] = $row;
        }

        return $this->data;
    }


    /**
     * @return string
     */
    public function render()
    {
        $this->articles = $this->getArticlesInRegion();

        $changes = array(
            'region' => $this->regionData,
            'data' => array(),
        );

        // post, video, gallery, none
        if (! empty($this->articles)) {
            foreach ($this->articles as $article) {
                if (empty($article['type'])) {
                    $article['type'] = 'post';
                }

                if (! empty($this->regionData['template_set_prefix'])) {
                    $displayTemplateSet = $this->regionData['template_set_prefix'] . '_entry_';
                } else {
                    $displayTemplateSet = 'news_entry_';
                }

                $template = $displayTemplateSet . $article['type'];

                $article = $this->fillInArticleGaps($article);

                $changes['data'] = $article;

                $this->finalHtml .= new template($template, $changes, '0');
            }

            // Simple Pagination
            $totalPages = ceil($this->totalArticles / $this->display);

            if ($totalPages > 1) {
                $prev = $this->page - 1;
                $next = $this->page + 1;

                $this->finalHtml .= '<div class="pagination">';

                if ($totalPages == $this->page) {
                    $this->finalHtml .= '<span class="page_last"><a href="?page=' . $prev . '&display=' . $this->display . '">&laquo; Previous</a></span>';
                    // $this->finalHtml .= '<span class="page_next"><a href="?page=' . $next . '&display=' . $this->display . '">Next &raquo;</a></span>';
                } else if ($this->page == 1) {
                    // $this->finalHtml .= '<span class="page_last"><a href="?page=' . $prev . '&display=' . $this->display . '">&laquo; Previous</a></span>';
                    $this->finalHtml .= '<span class="page_next"><a href="?page=' . $next . '&display=' . $this->display . '">Next &raquo;</a></span>';
                } else {
                    $this->finalHtml .= '<span class="page_last"><a href="?page=' . $prev . '&display=' . $this->display . '">&laquo; Previous</a></span>';
                    $this->finalHtml .= '<span class="page_next"><a href="?page=' . $next . '&display=' . $this->display . '">Next &raquo;</a></span>';
                }

                $this->finalHtml .= '</div>';
            }

        } else {
            $this->finalHtml = new template('news_entry_none', $changes, '0');
        }

        return $this->finalHtml;
    }


    /**
     * @param $content
     *
     * @return string
     */
    protected function getSnippet($content)
    {
        $exp = explode("</p>", $content);

        $first = strip_tags($exp['0']);
        if (! empty($first)) {
            return $first;
        } else {
            $first = strip_tags($content);

            $cut = (! empty($this->regionData['snippet_length'])) ? $this->regionData['snippet_length'] : 100;

            if (strlen($content) > $cut) {
                return substr($content, 0, $cut) . '...';
            } else {
                return substr($content, 0, $cut);
            }
        }
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function getArticle($id, $skipLoginCheck = false)
    {
        $data = $this->get_array("
            SELECT *
            FROM `ppSD_login_announcements`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");

        if ($skipLoginCheck) {
            return $this->fillInArticleGaps($data);
        }

        if (! $this->loggedIn && $data['public'] != '1') {
            return array(
                'error' => true,
                'msg' => 'Not Logged In',
            );
        } else {
            return $this->fillInArticleGaps($data);
        }
    }


    /**
     * @param $id
     *
     * @return string
     */
    public function removeFeeds($id)
    {
        return $this->delete("
            DELETE FROM `ppSD_login_annoucement_location`
            WHERE `news_id`='" . $this->mysql_clean($id) . "'
        ");
    }


    /**
     * @param $postId
     * @param $feeds
     *
     * @return bool
     */
    public function addToFeed($postId, $feeds)
    {
        if (! is_array($feeds)) {
            $feeds = array(
                $feeds
            );
        }

        foreach ($feeds as $aFeed) {
            $this->insert("
                INSERT INTO `ppSD_login_annoucement_location` (`news_id`,`region`)
                VALUES (
                    '" . $this->mysql_clean($postId) . "',
                    '" . $this->mysql_clean($aFeed) . "'
                )
            ");
        }

        return true;
    }


    /**
     * @param $id
     *
     * @return array
     */
    public function postRegions($id)
    {
        $all = array();

        $query = $this->run_query("
            SELECT `region`
            FROM ppSD_login_annoucement_location
            WHERE `news_id`='" . $this->mysql_clean($id) . "'
        ");

        while ($row = $query->fetch()) {
            $all[] = $row['region'];
        }

        return $all;
    }

    /**
     * @param $src
     *
     * @return string
     */
    public function buildMediaLink($src)
    {
        if (substr($src, 0, 4) == 'http') {
            return $src;
        } else {
            return PP_URL . '/custom/uploads/' . $src;
        }
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        $all = array();

        $query = $this->run_query("
            SELECT *
            FROM ppSD_login_announcement_regions
            ORDER BY `name` ASC
        ");

        while ($row = $query->fetch()) {
            $all[] = $row;
        }

        return $all;
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function getRegion($id)
    {
        return $this->get_array("
            SELECT *
            FROM `ppSD_login_announcement_regions`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }

    /**
     * @param $regionTag
     *
     * @return array
     */
    public function getPostsInRegion($regionTag)
    {
        return $this->getArticlesInRegion($regionTag);
    }


    /**
     * @param $article
     *
     * @return mixed
     */
    public function fillInArticleGaps($article)
    {
        $article['published'] = format_date($article['starts'], '', 1);

        if ($article['type'] == 'post') {
            $article['snippet'] = $this->getSnippet($article['content']);
        } else {
            $article['snippet'] = $article['content'];
        }

        if ($article['type'] == 'video') {

            $check = strtolower($article['media']);
            if (strpos($check, 'youtube.com') !== false) {
                $article['media'] = str_replace("watch?v=", "embed/", $article['media']);
            } else if (strpos($check, 'vimeo.com') !== false) {
                $geta = explode('?', $article['media']);
                $getb = explode('/', $geta['0']);
                $id = $getb[sizeof($getb)-1];
                $article['media'] = 'https://player.vimeo.com/video/' . $id;
            } else {
                // $article['media'] = '';
            }

            $article['player'] = '<iframe class="news_player" src="' . $article['media'] . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        } else {
            $path = PP_PATH . '/custom/uploads/' . $article['media'];
            if (file_exists($path)) {
                $article['img'] = '<a href="' . PP_URL . '/news.php?id=' . $article['id'] . '"><img src="' . PP_URL . '/custom/uploads/' . $article['media'] . '" alt="' . $article['title'] . '" class="news_media_' . $article['media_location'] . '" /></a>';
            } else {
                $article['img'] = '';
            }
        }

        return $article;
    }

}