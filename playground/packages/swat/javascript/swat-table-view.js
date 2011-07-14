/**
 * JavaScript for the SwatTableView widget
 *
 * @param id string Id of the matching {@link SwatTableView}.
 */
function SwatTableView(id)
{
	SwatTableView.superclass.constructor.call(this, id);

	this.table_node = document.getElementById(this.id);

	// look for tbody node
	var tbody_node = null;
	for (var i = 0; i < this.table_node.childNodes.length; i++) {
		if (this.table_node.childNodes[i].nodeName == 'TBODY') {
			tbody_node = this.table_node.childNodes[i];
			break;
		}
	}

	// no tbody node, so item rows are directly in table node
	if (tbody_node === null)
		tbody_node = this.table_node;

	for (var i = 0; i < tbody_node.childNodes.length; i++) {
		if (tbody_node.childNodes[i].nodeName == 'TR')
			this.items.push(tbody_node.childNodes[i]);
	}
}

YAHOO.lang.extend(SwatTableView, SwatView, {

/**
 * Gets an item node in a table-view
 *
 * The item node is the closest parent table row element.
 *
 * @param DOMElement node the arbitrary descendant node.
 *
 * @return DOMElement the item node.
 */
getItemNode: function(node)
{
	var row_node = node;

	// search for containing table row element
	while (row_node.nodeName != 'TR' && row_node.nodeName != 'BODY')
		row_node = row_node.parentNode;

	// we reached the body element without finding the row node
	if (row_node.nodeName == 'BODY')
		row_node = node;

	return row_node;
}

});

/**
 * Selects an item node in this table-view
 *
 * For table-views, this method also highlights selected item rows.
 *
 * @param DOMElement node an arbitrary descendant node of the item node to be
 *                         selected.
 * @param String selector an identifier of the object that selected the item
 *                         node.
 */
SwatTableView.prototype.selectItem = function(node, selector)
{
	SwatTableView.superclass.selectItem.call(this, node, selector);

	var row_node = this.getItemNode(node);

	// highlight table row of selected item in this view
	if (this.isSelected(row_node)) {
		if (YAHOO.util.Dom.hasClass(row_node, 'odd')) {
			YAHOO.util.Dom.removeClass(row_node, 'odd');
			YAHOO.util.Dom.addClass(row_node, 'highlight-odd');
		} else if (!(YAHOO.util.Dom.hasClass(row_node, 'highlight-odd') ||
			YAHOO.util.Dom.hasClass(row_node, 'highlight'))) {
			YAHOO.util.Dom.addClass(row_node, 'highlight');
		}
	}
}

/**
 * Deselects an item node in this table-view
 *
 * For table-views, this method also unhighlights deselected item rows.
 *
 * @param DOMElement node an arbitrary descendant node of the item node to be
 *                         deselected.
 * @param String selector an identifier of the object that deselected the item
 *                         node.
 */
SwatTableView.prototype.deselectItem = function(node, selector)
{
	SwatTableView.superclass.deselectItem.call(this, node, selector);

	var row_node = this.getItemNode(node);

	// unhighlight table row of item in this view
	if (!this.isSelected(row_node)) {
		if (YAHOO.util.Dom.hasClass(row_node, 'highlight-odd')) {
			YAHOO.util.Dom.removeClass(row_node, 'highlight-odd');
			YAHOO.util.Dom.addClass(row_node, 'odd');
		} else if (YAHOO.util.Dom.hasClass(row_node, 'highlight')) {
			YAHOO.util.Dom.removeClass(row_node, 'highlight');
		}
	}
}
