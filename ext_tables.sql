#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content
(
	content_block text
);


#
# Table structure for table 'tx_contentblocks_reg_api_collection'
#
CREATE TABLE tx_contentblocks_reg_api_collection
(
	content_block                     text,
	content_block_foreign_field       text,
	content_block_foreign_table_field text,
	content_block_field_identifier    text
);


#KEY language (l10n_parent,sys_language_uid)
