# frozen_string_literal: true

ActiveRecord::Schema[7.0].define do
  enable_extension "plpgsql" if respond_to?(:enable_extension)

  # == USERS ==
  create_table "users", force: :cascade do |t|
    t.string   "name",         null: false
    t.string   "email",        null: false
    t.string   "password",     null: false
    t.string   "role",         null: false, default: "staff"
    t.boolean  "active",       null: false, default: true
    t.datetime "created_at",   null: false
    t.datetime "updated_at",   null: false
  end
  add_index "users", ["email"], name: "index_users_on_email", unique: true

  # == IMAGES ==
  create_table "images", force: :cascade do |t|
    t.string   "file_path",  null: false
    t.string   "disk",       null: false, default: "public"
    t.string   "alt"
    t.integer  "width"
    t.integer  "height"
    t.string   "mime"
    t.string   "model_type", null: false
    t.bigint   "model_id",   null: false
    t.integer  "order",      null: false, default: 1
    t.boolean  "active",     null: false, default: true
    t.jsonb    "extra_attributes"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "images", ["model_type", "model_id"], name: "index_images_on_model"

  # == SETTINGS ==
  create_table "settings", force: :cascade do |t|
    t.bigint   "logo_image_id"
    t.bigint   "favicon_image_id"
    t.string   "site_name"
    t.string   "hotline"
    t.string   "address"
    t.string   "hours"
    t.string   "email"
    t.string   "meta_default_title"
    t.text     "meta_default_description"
    t.string   "meta_default_keywords"
    t.jsonb    "extra"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_foreign_key "settings", "images", column: "logo_image_id"
  add_foreign_key "settings", "images", column: "favicon_image_id"

  # == SOCIAL_LINKS ==
  create_table "social_links", force: :cascade do |t|
    t.string   "platform",  null: false
    t.string   "url",       null: false
    t.bigint   "icon_image_id"
    t.integer  "order",     null: false, default: 0
    t.boolean  "active",    null: false, default: true
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_foreign_key "social_links", "images", column: "icon_image_id"

  # == PRODUCT CATEGORIES ==
  create_table "product_categories", force: :cascade do |t|
    t.string   "name",  null: false
    t.string   "slug",  null: false
    t.text     "description"
    t.integer  "order", null: false, default: 0
    t.boolean  "active", null: false, default: true
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "product_categories", ["slug"], name: "index_product_categories_on_slug", unique: true

  # == PRODUCT TYPES ==
  create_table "product_types", force: :cascade do |t|
    t.string   "name",  null: false
    t.string   "slug",  null: false
    t.text     "description"
    t.integer  "order", null: false, default: 0
    t.boolean  "active", null: false, default: true
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "product_types", ["slug"], name: "index_product_types_on_slug", unique: true

  # == CATALOG ATTRIBUTE GROUPS ==
  create_table "catalog_attribute_groups", force: :cascade do |t|
    t.string   "code",          null: false
    t.string   "name",          null: false
    t.string   "filter_type",   null: false, default: "multi"
    t.boolean  "is_filterable", null: false, default: true
    t.boolean  "is_primary",    null: false, default: false
    t.integer  "position",      null: false, default: 0
    t.jsonb    "display_config"
    t.datetime "created_at",    null: false
    t.datetime "updated_at",    null: false
  end
  add_index "catalog_attribute_groups", ["code"], name: "index_catalog_attribute_groups_on_code", unique: true

  # == CATALOG TERMS ==
  create_table "catalog_terms", force: :cascade do |t|
    t.bigint   "group_id",   null: false
    t.bigint   "parent_id"
    t.string   "name",       null: false
    t.string   "slug",       null: false
    t.text     "description"
    t.string   "icon_type"
    t.string   "icon_value"
    t.jsonb    "metadata"
    t.boolean  "is_active",  null: false, default: true
    t.integer  "position",   null: false, default: 0
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "catalog_terms", ["group_id", "slug"], name: "index_catalog_terms_on_group_id_and_slug", unique: true
  add_index "catalog_terms", ["parent_id"], name: "index_catalog_terms_on_parent_id"
  add_foreign_key "catalog_terms", "catalog_attribute_groups", column: "group_id"
  add_foreign_key "catalog_terms", "catalog_terms", column: "parent_id"

  # == PRODUCTS ==
  create_table "products", force: :cascade do |t|
    t.string   "name",        null: false
    t.string   "slug",        null: false
    t.bigint   "product_category_id", null: false
    t.bigint   "type_id",            null: false
    t.text     "description"
    t.bigint   "price",       null: false, default: 0
    t.bigint   "original_price", null: false, default: 0
    t.decimal  "alcohol_percent", precision: 5, scale: 2
    t.integer  "volume_ml"
    t.jsonb    "badges"
    t.boolean  "active",      null: false, default: true
    t.string   "meta_title"
    t.text     "meta_description"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "products", ["slug"], name: "index_products_on_slug", unique: true
  add_index "products", ["type_id", "product_category_id"], name: "index_products_type_category"
  add_foreign_key "products", "product_categories"
  add_foreign_key "products", "product_types", column: "type_id"

  # == PRODUCT TERM ASSIGNMENTS ==
  create_table "product_term_assignments", force: :cascade do |t|
    t.bigint   "product_id",  null: false
    t.bigint   "term_id",     null: false
    t.boolean  "is_primary",  null: false, default: false
    t.integer  "position",    null: false, default: 0
    t.jsonb    "extra"
    t.datetime "created_at",  null: false
    t.datetime "updated_at",  null: false
  end
  add_index "product_term_assignments", ["product_id", "term_id"], name: "index_product_term_assignments_on_product_and_term", unique: true
  add_index "product_term_assignments", ["term_id", "product_id"], name: "index_product_term_assignments_on_term_and_product"
  add_foreign_key "product_term_assignments", "products"
  add_foreign_key "product_term_assignments", "catalog_terms", column: "term_id"

  # == ARTICLES ==
  create_table "articles", force: :cascade do |t|
    t.string   "title",      null: false
    t.string   "slug",       null: false
    t.text     "excerpt"
    t.text     "content"
    t.bigint   "author_id",  null: false
    t.boolean  "active",     null: false, default: true
    t.string   "meta_title"
    t.text     "meta_description"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "articles", ["slug"], name: "index_articles_on_slug", unique: true
  add_foreign_key "articles", "users", column: "author_id"

  # == MENUS ==
  create_table "menus", force: :cascade do |t|
    t.string   "title"
    t.bigint   "term_id"
    t.string   "type",        null: false, default: "standard"
    t.string   "href"
    t.jsonb    "config"
    t.integer  "order",       null: false, default: 0
    t.boolean  "active",      null: false, default: true
    t.datetime "created_at",  null: false
    t.datetime "updated_at",  null: false
  end
  add_index "menus", ["active", "order"], name: "index_menus_on_active_and_order"
  add_index "menus", ["term_id", "type"], name: "index_menus_on_term_and_type"
  add_foreign_key "menus", "catalog_terms", column: "term_id"

  # == MENU BLOCKS ==
  create_table "menu_blocks", force: :cascade do |t|
    t.bigint   "menu_id",            null: false
    t.string   "title",              null: false
    t.bigint   "attribute_group_id"
    t.integer  "max_terms"
    t.jsonb    "config"
    t.integer  "order",              null: false, default: 0
    t.boolean  "active",             null: false, default: true
    t.datetime "created_at",         null: false
    t.datetime "updated_at",         null: false
  end
  add_index "menu_blocks", ["menu_id", "order"], name: "index_menu_blocks_on_menu_and_order"
  add_index "menu_blocks", ["attribute_group_id", "order"], name: "index_menu_blocks_on_group_and_order"
  add_foreign_key "menu_blocks", "menus"
  add_foreign_key "menu_blocks", "catalog_attribute_groups", column: "attribute_group_id"

  # == MENU BLOCK ITEMS ==
  create_table "menu_block_items", force: :cascade do |t|
    t.bigint   "menu_block_id", null: false
    t.bigint   "term_id"
    t.string   "label"
    t.string   "href"
    t.string   "badge"
    t.jsonb    "meta"
    t.integer  "order",        null: false, default: 0
    t.boolean  "active",       null: false, default: true
    t.datetime "created_at",   null: false
    t.datetime "updated_at",   null: false
  end
  add_index "menu_block_items", ["menu_block_id", "order"], name: "index_menu_block_items_on_block_and_order"
  add_index "menu_block_items", ["term_id", "menu_block_id"], name: "index_menu_block_items_on_term"
  add_foreign_key "menu_block_items", "menu_blocks"
  add_foreign_key "menu_block_items", "catalog_terms", column: "term_id"

  # == HOME COMPONENTS ==
  create_table "home_components", force: :cascade do |t|
    t.string   "type",   null: false
    t.jsonb    "config"
    t.integer  "order",  null: false, default: 0
    t.boolean  "active", null: false, default: true
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  # == TRACKING ==
  create_table "visitors", force: :cascade do |t|
    t.string   "anon_id",      null: false
    t.string   "ip_hash",      null: false
    t.string   "user_agent"
    t.datetime "first_seen_at"
    t.datetime "last_seen_at"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "visitors", ["anon_id"], name: "index_visitors_on_anon_id", unique: true

  create_table "visitor_sessions", force: :cascade do |t|
    t.bigint   "visitor_id",  null: false
    t.datetime "started_at",  null: false
    t.datetime "ended_at"
    t.jsonb    "metadata"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "visitor_sessions", ["visitor_id", "started_at"], name: "index_visitor_sessions_on_visitor_and_started"
  add_foreign_key "visitor_sessions", "visitors"

  create_table "tracking_events", force: :cascade do |t|
    t.bigint   "visitor_id",  null: false
    t.bigint   "session_id",  null: false
    t.string   "event_type",  null: false
    t.bigint   "product_id"
    t.bigint   "article_id"
    t.jsonb    "metadata"
    t.datetime "occurred_at", null: false
    t.datetime "created_at",  null: false
    t.datetime "updated_at",  null: false
  end
  add_index "tracking_events", ["event_type", "occurred_at"], name: "index_tracking_events_on_type_and_time"
  add_foreign_key "tracking_events", "visitors", column: "visitor_id"
  add_foreign_key "tracking_events", "visitor_sessions", column: "session_id"
  add_foreign_key "tracking_events", "products", column: "product_id"
  add_foreign_key "tracking_events", "articles", column: "article_id"

  create_table "tracking_event_aggregates_daily", force: :cascade do |t|
    t.date     "date",       null: false
    t.string   "event_type", null: false
    t.bigint   "product_id"
    t.bigint   "article_id"
    t.bigint   "views",  null: false, default: 0
    t.bigint   "clicks", null: false, default: 0
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end
  add_index "tracking_event_aggregates_daily", ["date", "event_type", "product_id", "article_id"], name: "index_tracking_event_aggregates_daily_on_keys", unique: true
  add_foreign_key "tracking_event_aggregates_daily", "products", column: "product_id"
  add_foreign_key "tracking_event_aggregates_daily", "articles", column: "article_id"

end

