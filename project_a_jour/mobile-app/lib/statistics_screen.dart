import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

const String baseUrl = 'http://10.0.2.2:8000'; // À adapter selon l'environnement

class StatisticsScreen extends StatefulWidget {
  const StatisticsScreen({Key? key}) : super(key: key);

  @override
  State<StatisticsScreen> createState() => _StatisticsScreenState();
}

class _StatisticsScreenState extends State<StatisticsScreen> {
  late Future<List<CheckInStat>> _futureStats;

  @override
  void initState() {
    super.initState();
    // Remplacer 1 par l'eventId réel si besoin
    _futureStats = fetchStats(eventId: 1);
  }

  Future<List<CheckInStat>> fetchStats({required int eventId}) async {
    final response = await http.get(Uri.parse('$baseUrl/api/events/$eventId/stats'));
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final List stats = data['stats'] ?? [];
      return stats.map((e) => CheckInStat.fromJson(e)).toList();
    } else {
      throw Exception('Failed to load statistics');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Statistics')),
      body: FutureBuilder<List<CheckInStat>>(
        future: _futureStats,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text('Error: \\${snapshot.error}'));
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text('No statistics yet.'));
          }
          final stats = snapshot.data!;
          return ListView.separated(
            itemCount: stats.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (context, i) {
              final stat = stats[i];
              return ListTile(
                leading: const Icon(Icons.bar_chart),
                title: Text('Date: \\${stat.date}'),
                trailing: Text('Check-ins: \\${stat.count}'),
              );
            },
          );
        },
      ),
    );
  }
}

class CheckInStat {
  final String date;
  final int count;

  CheckInStat({required this.date, required this.count});

  factory CheckInStat.fromJson(Map<String, dynamic> json) {
    return CheckInStat(
      date: json['checkin_date'] ?? '-',
      count: int.tryParse(json['total_checkins'].toString()) ?? 0,
    );
  }
}
