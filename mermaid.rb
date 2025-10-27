# frozen_string_literal: true

ActiveRecord::Schema[7.0].define do
  enable_extension "plpgsql" if respond_to?(:enable_extension)

  create_table "users", force: :cascade do |t|
    t.string  "name"
    t.string  "email"
    t.string  "password"
    t.string  "role"
    t.boolean "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "users", ["email"], name: "index_users_on_email", unique: true

  create_table "audit_logs", force: :cascade do |t|
    t.bigint   "user_id"
    t.string   "action"
    t.text     "details_json"
    t.datetime "created_at"
  end
  add_index "audit_logs", ["user_id"], name: "index_audit_logs_on_user_id"
  add_foreign_key "audit_logs", "users", column: "user_id"

  create_table "images", force: :cascade do |t|
    t.string   "url"
    t.string   "alt_text"
    t.text     "caption"
    t.string   "model_type"
    t.bigint   "model_id"
    t.integer  "order"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "images", ["model_type", "model_id"], name: "index_images_on_model"

  create_table "settings", force: :cascade do |t|
    t.bigint   "logo_image_id"
    t.bigint   "favicon_image_id"
    t.string   "site_name"
    t.string   "hotline"
    t.string   "address"
    t.string   "hours"
    t.string   "email"
    t.string   "meta_default_title"
    t.string   "meta_default_description"
    t.string   "meta_default_keywords"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "settings", ["logo_image_id"], name: "index_settings_on_logo_image_id"
  add_index "settings", ["favicon_image_id"], name: "index_settings_on_favicon_image_id"
  add_foreign_key "settings", "images", column: "logo_image_id"
  add_foreign_key "settings", "images", column: "favicon_image_id"

  create_table "social_links", force: :cascade do |t|
    t.string   "name"
    t.string   "url"
    t.bigint   "icon_image_id"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "social_links", ["icon_image_id"], name: "index_social_links_on_icon_image_id"
  add_foreign_key "social_links", "images", column: "icon_image_id"

  create_table "product_categories", force: :cascade do |t|
    t.string   "name"
    t.string   "slug"
    t.text     "description"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "product_categories", ["slug"], name: "index_product_categories_on_slug", unique: true

  create_table "product_types", force: :cascade do |t|
    t.string   "name"
    t.string   "slug"
    t.text     "description"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "product_types", ["slug"], name: "index_product_types_on_slug", unique: true

  create_table "brands", force: :cascade do |t|
    t.string   "name"
    t.string   "slug"
    t.text     "description"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "brands", ["slug"], name: "index_brands_on_slug", unique: true

  create_table "countries", force: :cascade do |t|
    t.string   "name"
    t.string   "slug"
    t.text     "description"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "countries", ["slug"], name: "index_countries_on_slug", unique: true

  create_table "regions", force: :cascade do |t|
    t.string   "name"
    t.string   "slug"
    t.bigint   "country_id"
    t.text     "description"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "regions", ["slug"], name: "index_regions_on_slug", unique: true
  add_index "regions", ["country_id"], name: "index_regions_on_country_id"
  add_foreign_key "regions", "countries", column: "country_id"

  create_table "grapes", force: :cascade do |t|
    t.string   "name"
    t.string   "slug"
    t.text     "description"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "grapes", ["slug"], name: "index_grapes_on_slug", unique: true

  create_table "products", force: :cascade do |t|
    t.string   "name"
    t.string   "slug"
    t.bigint   "product_category_id"
    t.bigint   "type_id"
    t.bigint   "brand_id"
    t.bigint   "country_id"
    t.bigint   "region_id"
    t.bigint   "grape_id"
    t.text     "description"
    t.integer  "volume_ml"
    t.decimal  "alcohol_percent", precision: 5,  scale: 2
    t.decimal  "price",           precision: 15, scale: 2
    t.decimal  "original_price",  precision: 15, scale: 2
    t.boolean  "active", default: true
    t.integer  "order"
    t.string   "meta_title"
    t.string   "meta_description"
    t.string   "meta_keywords"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "products", ["slug"], name: "index_products_on_slug", unique: true
  add_index "products", ["product_category_id"], name: "index_products_on_product_category_id"
  add_index "products", ["type_id"], name: "index_products_on_type_id"
  add_index "products", ["brand_id"], name: "index_products_on_brand_id"
  add_index "products", ["country_id"], name: "index_products_on_country_id"
  add_index "products", ["region_id"], name: "index_products_on_region_id"
  add_index "products", ["grape_id"], name: "index_products_on_grape_id"
  add_foreign_key "products", "product_categories", column: "product_category_id"
  add_foreign_key "products", "product_types",     column: "type_id"
  add_foreign_key "products", "brands",            column: "brand_id"
  add_foreign_key "products", "countries",         column: "country_id"
  add_foreign_key "products", "regions",           column: "region_id"
  add_foreign_key "products", "grapes",            column: "grape_id"

  create_table "product_grapes", id: false, force: :cascade do |t|
    t.bigint  "product_id", null: false
    t.bigint  "grape_id",   null: false
    t.integer "order"
  end
  add_index "product_grapes", ["product_id", "grape_id"], name: "index_product_grapes_on_product_and_grape", unique: true
  add_index "product_grapes", ["product_id"], name: "index_product_grapes_on_product_id"
  add_index "product_grapes", ["grape_id"],   name: "index_product_grapes_on_grape_id"
  add_foreign_key "product_grapes", "products", column: "product_id"
  add_foreign_key "product_grapes", "grapes",   column: "grape_id"

  create_table "product_regions", id: false, force: :cascade do |t|
    t.bigint  "product_id", null: false
    t.bigint  "region_id",  null: false
    t.integer "order"
  end
  add_index "product_regions", ["product_id", "region_id"], name: "index_product_regions_on_product_and_region", unique: true
  add_index "product_regions", ["product_id"], name: "index_product_regions_on_product_id"
  add_index "product_regions", ["region_id"],  name: "index_product_regions_on_region_id"
  add_foreign_key "product_regions", "products", column: "product_id"
  add_foreign_key "product_regions", "regions",  column: "region_id"

  create_table "articles", force: :cascade do |t|
    t.string   "title"
    t.string   "slug"
    t.text     "content"
    t.boolean  "active", default: true
    t.string   "meta_title"
    t.string   "meta_description"
    t.string   "meta_keywords"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "articles", ["slug"], name: "index_articles_on_slug", unique: true

  create_table "menus", force: :cascade do |t|
    t.string   "title"
    t.string   "type"
    t.string   "href"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "menu_blocks", force: :cascade do |t|
    t.bigint   "menu_id"
    t.string   "title"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "menu_blocks", ["menu_id"], name: "index_menu_blocks_on_menu_id"
  add_foreign_key "menu_blocks", "menus", column: "menu_id"

  create_table "menu_block_items", force: :cascade do |t|
    t.bigint   "menu_block_id"
    t.string   "label"
    t.string   "href"
    t.string   "badge"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "menu_block_items", ["menu_block_id"], name: "index_menu_block_items_on_menu_block_id"
  add_foreign_key "menu_block_items", "menu_blocks", column: "menu_block_id"

  create_table "home_components", force: :cascade do |t|
    t.string   "type"
    t.text     "config_json"
    t.integer  "order"
    t.boolean  "active"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "visitors", force: :cascade do |t|
    t.string   "ip_address"
    t.string   "user_agent"
    t.string   "device"
    t.string   "country"
    t.datetime "first_seen_at"
    t.datetime "last_seen_at"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "visitor_sessions", force: :cascade do |t|
    t.bigint   "visitor_id"
    t.string   "session_key"
    t.datetime "start_time"
    t.datetime "end_time"
    t.integer  "pages_viewed"
    t.datetime "created_at"
    t.datetime "updated_at"
  end
  add_index "visitor_sessions", ["visitor_id"], name: "index_visitor_sessions_on_visitor_id"
  add_foreign_key "visitor_sessions", "visitors", column: "visitor_id"

  create_table "tracking_events", force: :cascade do |t|
    t.bigint   "visitor_id"
    t.bigint   "visitor_session_id"
    t.string   "event_type"
    t.string   "page_url"
    t.bigint   "product_id"
    t.string   "country_snapshot"
    t.text     "data_json"
    t.datetime "created_at"
  end
  add_index "tracking_events", ["visitor_id"], name: "index_tracking_events_on_visitor_id"
  add_index "tracking_events", ["visitor_session_id"], name: "index_tracking_events_on_visitor_session_id"
  add_index "tracking_events", ["product_id"], name: "index_tracking_events_on_product_id"
  add_foreign_key "tracking_events", "visitors", column: "visitor_id"
  add_foreign_key "tracking_events", "visitor_sessions", column: "visitor_session_id"
  add_foreign_key "tracking_events", "products", column: "product_id"

  create_table "url_redirects", force: :cascade do |t|
    t.string   "model_type"
    t.bigint   "model_id"
    t.string   "from_slug"
    t.string   "to_slug"
    t.boolean  "active", default: true
    t.datetime "created_at"
  end
  add_index "url_redirects", ["from_slug"], name: "index_url_redirects_on_from_slug", unique: true
  add_index "url_redirects", ["model_type", "model_id"], name: "index_url_redirects_on_model"

end
