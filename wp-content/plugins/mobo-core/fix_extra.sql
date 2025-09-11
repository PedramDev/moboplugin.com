
DELETE FROM wp_postmeta
WHERE meta_id IN (
    SELECT im.meta_id
	FROM wp_postmeta im
	WHERE (im.post_id, im.meta_key) IN (
		SELECT im_inner.post_id, im_inner.meta_key
		FROM wp_postmeta im_inner
		GROUP BY im_inner.post_id, im_inner.meta_key
		HAVING COUNT(*) > 1
	)
);