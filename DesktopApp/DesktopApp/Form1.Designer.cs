namespace DesktopApp
{
    partial class Form1
    {
        /// <summary>
        ///  Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        ///  Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        ///  Required method for Designer support - do not modify
        ///  the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(Form1));
            this.btnGeAll = new System.Windows.Forms.Button();
            this.txtResponse = new System.Windows.Forms.RichTextBox();
            this.label1 = new System.Windows.Forms.Label();
            this.button1 = new System.Windows.Forms.Button();
            this.button2 = new System.Windows.Forms.Button();
            this.pictureBox1 = new System.Windows.Forms.PictureBox();
            this.Get = new System.Windows.Forms.Button();
            this.textBox1 = new System.Windows.Forms.TextBox();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).BeginInit();
            this.SuspendLayout();
            // 
            // btnGeAll
            // 
            this.btnGeAll.Location = new System.Drawing.Point(846, 144);
            this.btnGeAll.Name = "btnGeAll";
            this.btnGeAll.Size = new System.Drawing.Size(162, 77);
            this.btnGeAll.TabIndex = 0;
            this.btnGeAll.Text = "Get all threads";
            this.btnGeAll.UseVisualStyleBackColor = true;
            this.btnGeAll.Click += new System.EventHandler(this.btnGeAll_Click);
            // 
            // txtResponse
            // 
            this.txtResponse.Location = new System.Drawing.Point(846, 247);
            this.txtResponse.Name = "txtResponse";
            this.txtResponse.Size = new System.Drawing.Size(815, 451);
            this.txtResponse.TabIndex = 1;
            this.txtResponse.Text = "";
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.Font = new System.Drawing.Font("Segoe UI", 40F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point);
            this.label1.ForeColor = System.Drawing.SystemColors.MenuHighlight;
            this.label1.LiveSetting = System.Windows.Forms.Automation.AutomationLiveSetting.Assertive;
            this.label1.Location = new System.Drawing.Point(283, 64);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(273, 72);
            this.label1.TabIndex = 4;
            this.label1.Text = "Kontolene";
            // 
            // button1
            // 
            this.button1.Location = new System.Drawing.Point(1936, 12);
            this.button1.Name = "button1";
            this.button1.Size = new System.Drawing.Size(75, 23);
            this.button1.TabIndex = 0;
            this.button1.Text = "Login";
            // 
            // button2
            // 
            this.button2.Location = new System.Drawing.Point(1844, 12);
            this.button2.Name = "button2";
            this.button2.Size = new System.Drawing.Size(75, 23);
            this.button2.TabIndex = 5;
            this.button2.Text = "Register";
            this.button2.UseVisualStyleBackColor = true;
            // 
            // pictureBox1
            // 
            this.pictureBox1.Image = ((System.Drawing.Image)(resources.GetObject("pictureBox1.Image")));
            this.pictureBox1.Location = new System.Drawing.Point(40, 215);
            this.pictureBox1.Name = "pictureBox1";
            this.pictureBox1.Size = new System.Drawing.Size(525, 483);
            this.pictureBox1.TabIndex = 6;
            this.pictureBox1.TabStop = false;
            // 
            // Get
            // 
            this.Get.Location = new System.Drawing.Point(1054, 144);
            this.Get.Name = "Get";
            this.Get.Size = new System.Drawing.Size(148, 50);
            this.Get.TabIndex = 7;
            this.Get.Text = "Get Thread by ID";
            this.Get.UseVisualStyleBackColor = true;
            this.Get.Click += new System.EventHandler(this.Get_Click);
            // 
            // textBox1
            // 
            this.textBox1.Location = new System.Drawing.Point(1077, 200);
            this.textBox1.Name = "textBox1";
            this.textBox1.Size = new System.Drawing.Size(100, 23);
            this.textBox1.TabIndex = 8;
            // 
            // Form1
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(7F, 15F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(2035, 833);
            this.Controls.Add(this.textBox1);
            this.Controls.Add(this.Get);
            this.Controls.Add(this.pictureBox1);
            this.Controls.Add(this.button2);
            this.Controls.Add(this.button1);
            this.Controls.Add(this.label1);
            this.Controls.Add(this.txtResponse);
            this.Controls.Add(this.btnGeAll);
            this.ForeColor = System.Drawing.SystemColors.ControlText;
            this.Name = "Form1";
            this.Text = "Form1";
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).EndInit();
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private Button btnGeAll;
        private RichTextBox txtResponse;
        private Label label1;
        private Button button1;
        private Button button2;
        private PictureBox pictureBox1;
        private Button Get;
        private TextBox textBox1;
    }
}