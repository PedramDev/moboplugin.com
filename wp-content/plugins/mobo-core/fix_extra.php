<?php


#DELETE FROM wp_postmeta
#WHERE meta_id IN (
#    SELECT im.meta_id
#	FROM wp_postmeta im
#	WHERE (im.post_id, im.meta_key) IN (
#		SELECT im_inner.post_id, im_inner.meta_key
#		FROM wp_postmeta im_inner
#		GROUP BY im_inner.post_id, im_inner.meta_key
#		HAVING COUNT(*) > 1
#	)
#);

#DELETE FROM 20DfZ_posts WHERE post_type = 'product_variation' 
#AND post_parent NOT IN 
#    ( SELECT ID FROM 20DfZ_posts WHERE post_type = 'product' );