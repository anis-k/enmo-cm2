-- res_letterbox
CREATE INDEX type_id_idx ON res_letterbox (type_id);
CREATE INDEX typist_idx ON res_letterbox (typist);
CREATE INDEX doc_date_idx ON res_letterbox (doc_date);
CREATE INDEX folders_system_id_idx ON res_letterbox (folders_system_id);
CREATE INDEX status_idx ON res_letterbox (status);
CREATE INDEX destination_idx ON res_letterbox (destination);
CREATE INDEX dest_user_idx ON res_letterbox (dest_user);
CREATE INDEX custom_t15_idx ON res_letterbox (custom_t15);
CREATE INDEX res_letterbox_docserver_id_idx ON res_letterbox (docserver_id);
CREATE INDEX res_letterbox_filename_idx ON res_letterbox (filename);

-- res_business
CREATE INDEX business_type_id_idx ON res_business (type_id);
CREATE INDEX business_typist_idx ON res_business (typist);
CREATE INDEX business_doc_date_idx ON res_business (doc_date);
CREATE INDEX business_folders_system_id_idx ON res_business (folders_system_id);
CREATE INDEX business_status_idx ON res_business (status);
CREATE INDEX business_destination_idx ON res_business (destination);
CREATE INDEX business_dest_user_idx ON res_business (dest_user);
CREATE INDEX business_custom_t1_idx ON res_business (custom_t1);
CREATE INDEX business_custom_t2_idx ON res_business (custom_t2);
CREATE INDEX business_custom_t3_idx ON res_business (custom_t3);
CREATE INDEX business_custom_t4_idx ON res_business (custom_t4);
CREATE INDEX business_custom_t5_idx ON res_business (custom_t5);
CREATE INDEX business_custom_t6_idx ON res_business (custom_t6);
CREATE INDEX res_business_docserver_id_idx ON res_business (docserver_id);
CREATE INDEX res_business_filename_idx ON res_business (filename);

-- res_attachments
CREATE INDEX res_id_master_idx ON res_attachments (res_id_master);

-- mlb_coll_ext
CREATE INDEX category_id_idx ON mlb_coll_ext (category_id);
CREATE INDEX exp_contact_id_idx ON mlb_coll_ext (exp_contact_id);
CREATE INDEX exp_user_id_idx ON mlb_coll_ext (exp_user_id);
CREATE INDEX dest_contact_id_idx ON mlb_coll_ext (dest_contact_id);
CREATE INDEX dest_user_id_idx ON mlb_coll_ext (dest_user_id);
CREATE INDEX admission_date_idx ON mlb_coll_ext (admission_date);
CREATE INDEX process_limit_date_idx ON mlb_coll_ext (process_limit_date);

-- business_coll_ext
CREATE INDEX business_category_id_idx ON business_coll_ext (category_id);
CREATE INDEX business_contact_id ON business_coll_ext (contact_id);
CREATE INDEX business_total_sum ON business_coll_ext (total_sum);

-- listinstance
CREATE INDEX res_id_listinstance_idx ON listinstance (res_id);
CREATE INDEX listinstance_coll_id_idx ON listinstance (coll_id);
CREATE INDEX sequence_idx ON listinstance (sequence);
CREATE INDEX item_id_idx ON listinstance (item_id);
CREATE INDEX item_type_idx ON listinstance (item_type);
CREATE INDEX item_mode_idx ON listinstance (item_mode);
CREATE INDEX listinstance_difflist_type_idx ON listinstance (difflist_type);

-- contacts
CREATE INDEX firstname_contacts_idx ON contacts (firstname);
CREATE INDEX lastname_contacts_idx ON contacts (lastname);
CREATE INDEX society_idx ON contacts (society);

-- doctypes_first_level
CREATE INDEX doctypes_first_level_label_idx ON doctypes_first_level (doctypes_first_level_label);

-- doctypes_second_level
CREATE INDEX doctypes_second_level_label_idx ON doctypes_second_level (doctypes_second_level_label);

-- doctypes
CREATE INDEX description_idx ON doctypes (description);

-- entities
CREATE INDEX entity_label_idx ON entities (entity_label);

-- folders
CREATE INDEX folder_name_idx ON folders (folder_name);
CREATE INDEX subject_idx ON folders (subject);

-- groupbasket_redirect
CREATE INDEX groupbasket_redirect_group_id_idx ON groupbasket_redirect (group_id);
CREATE INDEX groupbasket_redirect_basket_id_idx ON groupbasket_redirect (basket_id);
CREATE INDEX groupbasket_redirect_action_id_idx ON groupbasket_redirect (action_id);
CREATE INDEX groupbasket_redirect_entity_id_idx ON groupbasket_redirect (entity_id);

-- history
CREATE INDEX table_name_idx ON history (table_name);
CREATE INDEX record_id_idx ON history (record_id);
CREATE INDEX event_type_idx ON history (event_type);
CREATE INDEX user_id_idx ON history (user_id);

-- notes
CREATE INDEX identifier_idx ON notes (identifier);
CREATE INDEX notes_user_id_idx ON notes (user_id);

-- saved_queries
CREATE INDEX user_id_queries_idx ON saved_queries (user_id);

-- users
CREATE INDEX lastname_users_idx ON users (lastname);
