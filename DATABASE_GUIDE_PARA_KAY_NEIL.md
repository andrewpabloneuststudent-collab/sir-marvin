# Database Guide para kay Neil 🚀

Hoy Neil! Kailangan mo i-update yung database mo para gumana yung bagong **Discount System** at **Manager Override**. Kung hindi mo 'to gagawin, mag-eerror yung POS natin.

### Paano i-setup?

1. **Import the Base Database**:
   Kung wala ka pang database o luma na yung sayo, i-import mo muna yung `main_db_backup.sql` sa phpMyAdmin.

2. **Run the Migration Script**:
   Eto yung pinaka-importante. I-run mo (o i-import) yung file na:
   `scratch/migration_discount_system.sql`
   
   **UPDATE**: Kung nag-eerror yung full migration, gamitin mo itong specific tables file:
   `scratch/specific_tables_export.sql`
   (Ito ay para sa `product_categories` at `override_log` lang para hindi mag-conflict sa ibang tables mo).

   **BAGO**: Kung kailangan mo i-update yung rules para sa VAT/Discount per item type, i-import mo ito:
   `scratch/product_classifications_export.sql`
   (Ito yung nag-seset kung aling items ang discountable at vatable).

   **Ano ang ginagawa nito?**
   - Nagdadagdag ng `has_vat`, `senior_discount`, at `pwd_discount` columns sa `product_categories` table.
   - Gagawa ng bagong table na `override_log` para ma-track kung sino yung nag-aapprove ng discount.

### Bakit kailangan nito?
- **Auto-VAT/Discount**: Ngayon, kapag yung category (halimbawa: Medicine) ay naka-set na "Senior Discount = YES", automatic na mag-aapply yung 12% discount sa POS. Hindi na kailangan i-manual.
- **Manager Override**: Kapag kailangan magbigay ng special discount, hihingi yung system ng username at password ng **Owner** o **Admin**. Lahat ng approval ay masesave sa `override_log` para walang dayaan.

### Categories Setup
Pagkatapos mo i-run yung SQL, pumunta ka sa **Product Management** tab. May makikita ka doon na **"Category Settings"** button. Doon mo i-check kung aling categories ang may VAT at aling categories ang eligible sa Senior/PWD discount.

Ayusin mo na 'to agad para ma-test na natin! 🍻
