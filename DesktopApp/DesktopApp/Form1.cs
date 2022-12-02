using DesktopApp.Shared;

namespace DesktopApp
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        private async void btnGeAll_Click(object sender, EventArgs e)
        {
            {
                var response = await RestHelper.GetALL();
                txtResponse.Text = RestHelper.BeautifyJson(response);
            }
        }

        private async void Get_Click(object sender, EventArgs e)
        {
            var response = await RestHelper.Get(textBox1.Text);
            txtResponse.Text = RestHelper.BeautifyJson(response);
        }
    }
}