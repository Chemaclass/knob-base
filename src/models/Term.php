<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Models;

/**
 * Tag
 * terms.term_id -> term_taxonomy.term_id
 * term_taxonomy.term_taxonomy_id -> term_relationships.term_taxonomy_id.
 * This model help us for to do querys and some operation about the tags of the diferents entries
 *
 * @author José María Valera Reales <@Chemaclass>
 */
class Term extends ModelBase
{

    public static $table = "terms";

    static $PK = 'term_id';

    /*
     * Some constants
     */
    const TRANSIENT_ALL_TAGS = 'ALL_TAGS';

    const TYPE_CATEGORY = 'category';

    const TYPE_TAG = 'post_tag';

    /*
     * Members
     */
    protected $total;

    /**
     * Return all categories
     *
     * @param array $args            
     *
     * @return array<Term>
     *
     * @link https://codex.wordpress.org/Function_Reference/get_terms
     */
    public static function getCategories($args = [])
    {
        return static::getTermsWithTotalsBy(self::TYPE_CATEGORY, $args);
    }

    /**
     * Return all tags
     *
     * @param array $args            
     *
     * @return array<Term>
     *
     * @link https://codex.wordpress.org/Function_Reference/get_terms
     */
    public static function getTags($args = [])
    {
        return static::getTermsWithTotalsBy(self::TYPE_TAG, $args);
    }

    /**
     *
     * @param string $type            
     * @param array $args            
     *
     * @return array
     */
    protected static function getTermsWithTotalsBy($type, $args = [])
    {
        if (! count($args)) {
            $args = [
                'orderby' => 'name,count',
                'hide_empty' => true
            ];
        }
        
        $terms = [];
        foreach (get_terms($type, $args) as $_t) {
            $term = Term::find($_t->term_id);
            $term->total = $_t->count;
            $terms[] = $term;
        }
        
        return $terms;
    }

    /**
     * Return the ID from the tag name
     *
     * @param string $name
     *            Term name
     * @param string $type
     *            Term type
     *            
     * @return int ID from the term name
     */
    public static function getTermIdbyName($name, $type = self::TYPE_TAG)
    {
        $tag = get_term_by('name', $name, $type);
        return ($tag) ? $tag->term_id : 0;
    }

    /**
     * Get the total of terms by termName and also the type (optional)
     *
     * @param string $name
     *            The term_slug
     * @param string $type
     *            The taxonomy_name
     *            
     * @return int
     */
    public static function getTotalBy($name, $type = '')
    {
        global $wpdb;
        $sql = "select count(*) from wp_v_generos_posts where term_slug like '%s'";
        if (empty($type)) {
            return $wpdb->get_var($wpdb->prepare($sql, $name));
        }
        $sql .= "and taxonomy_name = '%s'";
        
        return $wpdb->get_var($wpdb->prepare($sql, $name, $type));
    }

    /**
     *
     * @return string the name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return string the slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get all tags
     *
     * @see http://codex.wordpress.org/Transients_API
     * @return array<Etiqueta> List with all tags
     */
    public static function getAll()
    {
        global $wpdb;
        if (false === ($results = get_transient(self::TRANSIENT_ALL_TAGS))) {
            $results = $wpdb->get_results('
				SELECT ta.term_taxonomy_id as taxonomy_id, name, slug, count(*) total
				FROM ' . $wpdb->prefix . 'term_taxonomy ta
				JOIN ' . $wpdb->prefix . 'terms te ON (te.term_id = ta.term_id)
				JOIN ' . $wpdb->prefix . 'term_relationships re ON (re.term_taxonomy_id = ta.term_taxonomy_id)
				WHERE taxonomy = "post_tag"
				GROUP BY name, slug, taxonomy_id
				ORDER BY total DESC, name, slug');
            set_transient(self::TRANSIENT_ALL_TAGS, $results, 12 * HOUR_IN_SECONDS);
        }
        return $results;
    }
}