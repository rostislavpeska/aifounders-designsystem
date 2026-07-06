/**
 * Table scroll wrapper — HARVESTED from AIF production (single.php inline
 * script, verbatim mechanism): wrap prose/article tables in a .table-scroll
 * container so wide tables scroll horizontally instead of overflowing the
 * page. Idempotent (skips already-wrapped tables). Hand-authored tables can
 * ship the wrapper in markup instead; this covers CMS content where markup
 * can't be authored.
 */
document.querySelectorAll('.article-layout__content table').forEach(function (t) {
	if (t.parentElement.classList.contains('table-scroll')) return;
	var w = document.createElement('div');
	w.className = 'table-scroll';
	t.parentNode.insertBefore(w, t);
	w.appendChild(t);
});
