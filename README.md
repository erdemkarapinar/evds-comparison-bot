# EVDS Comparison Script

Bu proje, Türkiye Cumhuriyet Merkez Bankası EVDS API üzerinden döviz kurlarını çekerek günlük karşılaştırma yapar ve sonuçları Telegram botuna gönderir.

## Kurulum

### 1. Telegram Bot Oluşturma
Telegram üzerinde **@BotFather** üzerinden bir bot oluşturun.  
Bot oluşturulduktan sonra aşağıdaki bilgileri alın:

- **Bot Token**
- **Chat ID**

Bu bilgileri `.env` dosyanıza aşağıdaki şekilde ekleyin:

```env ```md
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id

### 2. EVDS API Key Alma

**@EVDS** sistemine giriş yaparak API anahtarınızı alın.
Anahtarı EvdsService içindeki `key` alana yerleştirin:

```key'
'key' = 'YOUR_EVDS_API_KEY';


3. Veritabanı Tablolarını Oluşturma

Projede kullanılacak tabloları oluşturmak için:

php artisan migrate

Artık her şey hazır!
Aşağıdaki komutu çalıştırdığınızda botunuza günlük kur değişim bildirimleri gönderilecektir:

php artisan app:evds-comparison


Bu komut ayrıca veritabanına 01/10/2025 tarihinden günümüze kadar olan tüm verileri otomatik olarak ekler.